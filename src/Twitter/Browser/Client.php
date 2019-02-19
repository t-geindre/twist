<?php

namespace Twist\Twitter\Browser;

use Twist\Twitter\Browser\Exception\BadCredentialsException;
use Twist\Twitter\Browser\Exception\NotLoggedInException;
use HeadlessChromium\Browser;
use HeadlessChromium\Page;
use Psr\Log\LoggerInterface;

class Client
{
    const LOGIN_URL = 'https://twitter.com/login';
    const SEARCH_URL = 'https://twitter.com/search-home';

    /** @var Page */
    private $page;

    /** @var array|null */
    private $requestHeaders;

    /** @var bool */
    private $loggedIn = false;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        Browser $browser,
        LoggerInterface $logger,
        string $headersInterceptorScript,
        string $requestProxyScript
    ) {
        $this->page = $browser->createPage();

        $this->page->addPreScript(
            file_get_contents($headersInterceptorScript)."\n".
            file_get_contents($requestProxyScript)
        );
        $this->logger = $logger;
    }

    public function login(string $username, string $password): bool
    {
        $this->logger->info('Logging in Twitter');

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

        $this->page->waitForReload(Page::DOM_CONTENT_LOADED);

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
        $this->logger->info('Fetching API credentials');

        if (null !== $this->requestHeaders) {
            return;
        }

        do {
            // Scroll down until there's an API call
            $this->page->evaluate("window.scrollTo(0,document.body.scrollHeight);");
            usleep(100000); // 100ms
            $this->requestHeaders = $this->page->evaluate('twitterContestHeadersLogs')->getReturnValue();
        } while (null === $this->requestHeaders);

        // Navigate to search page, avoid overload
        $this->page->navigate(self::SEARCH_URL);
        $this->page->waitForReload();
    }

    protected function assertLoggedIn(): void
    {
        if (!$this->loggedIn) {
            throw new NotLoggedInException('Login is required before making any other browser call');
        }
    }
}


