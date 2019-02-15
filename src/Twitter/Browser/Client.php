<?php

namespace App\Twitter\Browser;

use App\Twitter\Browser\Exception\BadCredentialsException;
use App\Twitter\Browser\Exception\NotLoggedInException;
use HeadlessChromium\Browser;
use HeadlessChromium\Page;

class Client
{
    const LOGIN_URL = 'https://twitter.com/login';

    /** @var Page */
    private $page;

    /** @var array|null */
    private $requestHeaders;

    /** @var bool */
    private $loggedIn = false;

    public function __construct(
        Browser $browser,
        string $headersInterceptorScript,
        string $requestProxyScript
    ) {
        $this->page = $browser->createPage();

        $this->page->addPreScript(
            file_get_contents($headersInterceptorScript)."\n".
            file_get_contents($requestProxyScript)
        );
    }

    public function login(string $username, string $password): bool
    {
        $this->page->navigate(self::LOGIN_URL);
        $this->page->waitForReload();

        if (strpos($this->page->getCurrentUrl(), self::LOGIN_URL) === false) {
            $this->fetchRequestHeaders();

            return $this->loggedIn = true;
        }

        $this->page->evaluate('
            $("form.signin input[type=text].email-input").val('.json_encode($username).');
            $("form.signin input[type=password]").val('.json_encode($password).');
            $("form.signin").submit();
        ');

        $this->page->waitForReload();

        if (strpos($this->page->getCurrentUrl(), self::LOGIN_URL) !== false) {
            throw new BadCredentialsException('Login failed, check username and password');
        }

        $this->fetchRequestHeaders();

        return $this->loggedIn = true;
    }

    public function request(array $settings)
    {
        $this->assertLoggedIn();

        $settings = array_merge_recursive(
            $settings,
            [
                'headers' => array_merge(
                    $this->getRequestHeaders(),
                    ['x-csrf-token' => $this->getCsrfToken()]
                ),
                'xhrFields' => ['withCredentials' => true]
            ]
        );

        $uid = uniqid();
        $this->page->evaluate('twitterContestRequest(
            '.json_encode($uid).',
            '.json_encode($settings).'
        )');

        do {
            usleep(100000); // 100ms
            $result = $this->page->evaluate('twitterContestGetRequestResult('.json_encode($uid).')')->getReturnValue();
        } while ($result['status'] === 'pending');

        return $result['data'];
    }

    public function getRequestHeaders(): ?array
    {
        $this->assertLoggedIn();

        return $this->requestHeaders;
    }

    public function getCsrfToken(): ?string
    {
        return $this->page->evaluate('(/ct0=([^;]+)/).exec(document.cookie)[1]')->getReturnValue();
    }

    protected function fetchRequestHeaders(): void
    {
        if (null !== $this->requestHeaders) {
            return;
        }

        do {
            // Scroll down until there's an API call
            $this->page->evaluate("window.scrollTo(0,document.body.scrollHeight);");
            usleep(100000); // 100ms
            $this->requestHeaders = $this->page->evaluate('twitterContestHeadersLogs')->getReturnValue();
        } while (null === $this->requestHeaders);

        // Reload page to avoid overload
        // todo navigate to a lighter page (search?)
        $this->page->navigate(self::LOGIN_URL);
        $this->page->waitForReload();
    }

    protected function assertLoggedIn(): void
    {
        if (!$this->loggedIn) {
            throw new NotLoggedInException('Login is required before making any other browser call');
        }
    }
}


