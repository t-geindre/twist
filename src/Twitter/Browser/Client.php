<?php

namespace Twist\Twitter\Browser;

use HeadlessChromium\Browser;
use HeadlessChromium\Exception\JavascriptException;
use HeadlessChromium\Exception\OperationTimedOut;
use HeadlessChromium\Page;
use HeadlessChromium\PageUtils\PageEvaluation;
use Psr\Log\LoggerInterface;

class Client
{
    const LOGIN_URL = 'https://twitter.com/login';
    const IDLE_URL = 'https://twitter.com/search-home';

    /** @var Page */
    private $page;

    /** @var array|null */
    private $requestHeaders;

    /** @var bool */
    private $loggedIn = false;

    /** @var LoggerInterface */
    private $logger;

    /** @var int */
    private $requestsCount = 0;

    /** @var Browser */
    private $browser;

    /** @var string */
    private $headersInterceptorScript;

    /** @var string */
    private $requestProxyScript;

    public function __construct(
        Browser $browser,
        LoggerInterface $logger,
        string $headersInterceptorScript,
        string $requestProxyScript
    ) {
        $this->logger = $logger;
        $this->browser = $browser;
        $this->headersInterceptorScript = $headersInterceptorScript;
        $this->requestProxyScript = $requestProxyScript;

        $this->createPage();
    }

    public function login(string $username, string $password): bool
    {
        $this->logger->info('Logging in Twitter');

        $this->page->navigate(self::LOGIN_URL)->waitForNavigation();

        if (strpos($this->page->getCurrentUrl(), self::LOGIN_URL) !== false) {
            $this->evaluate('
                $("form.signin input[type=text].email-input").val('.json_encode($username).');
                $("form.signin input[type=password]").val('.json_encode($password).');
                $("form.signin").submit();
            ');

            $this->page->waitForReload(Page::DOM_CONTENT_LOADED);

            if (strpos($this->page->getCurrentUrl(), self::LOGIN_URL) !== false) {
                throw new \RuntimeException('Login failed, check username and password');
            }
        }

        $this->fetchRequestHeaders();
        $this->reloadPage();

        return $this->loggedIn = true;
    }

    public function request(array $settings)
    {
        $this->assertLoggedIn();

        // Reload page to avoid overload
        if ($this->requestsCount++ > 300) {
            $this->requestsCount = 0;
            $this->reloadPage();
        }

        $settings = array_merge_recursive($settings, [
            'headers' => array_merge($this->getRequestHeaders(), ['x-csrf-token' => $this->getCsrfToken()]),
            'xhrFields' => ['withCredentials' => true]
        ]);

        $uid = uniqid();
        $this->evaluate('twist.sendRequest('.json_encode($uid).', '.json_encode($settings).')');

        for(;;) {
            $result = $this->evaluate('twist.getRequestResult('.json_encode($uid).')')->getReturnValue();
            if ($result['status'] === 'pending') {
                usleep(100000); // 100ms
                continue;
            }
            break;
        }

        if ($result['status'] === 'failed') {
            throw new \RuntimeException(sprintf(
                'An error occurred while requesting "%s", status: "%s"',
                $settings['url'] ?? 'none',
                $result['code']
            ));
        }

        return $result['data'];
    }

    public function evaluate(string $script, bool $handleException = true): PageEvaluation
    {
        try {
            return $this->page->evaluate($script);
        } catch (OperationTimedOut $e) {
            if ($handleException) {
                $this->reloadPage();
                return $this->evaluate($script, false);
            }
            throw $e;
        }
    }

    public function getRequestHeaders(): ?array
    {
        $this->assertLoggedIn();

        return $this->requestHeaders;
    }

    public function getCsrfToken(): ?string
    {
        $this->assertLoggedIn();

        try {
            return $this->evaluate('(/ct0=([^;]+)/).exec(document.cookie)[1]')->getReturnValue();
        } catch (JavascriptException $e) {
            throw new \RuntimeException('Unable to retriew CSRF Token', 0, $e);
        }
    }

    protected function fetchRequestHeaders(): void
    {
        $this->logger->info('Fetching API credentials');

        if (null !== $this->requestHeaders) {
            return;
        }

        do {
            // Scroll down until there's an API call
            $this->evaluate("window.scrollTo(0,document.body.scrollHeight);");
            usleep(100000); // 100ms
            $this->requestHeaders = $this->evaluate('twist.getInterceptedHeaders()')->getReturnValue();
        } while (false === $this->requestHeaders);
    }

    protected function reloadPage()
    {
        $url = null;
        if (null !== $this->page) {
            $url = $this->page->getCurrentUrl();
            $this->page->close();
        }

        $this->createPage();
        $this->page->navigate($url ? $url : self::IDLE_URL)->waitForNavigation();
    }

    protected function createPage(): void
    {
        $this->page = $this->browser->createPage();

        $this->page->addPreScript(
            file_get_contents($this->headersInterceptorScript)."\n".
            file_get_contents($this->requestProxyScript)
        );
    }

    protected function assertLoggedIn(): void
    {
        if (!$this->loggedIn) {
            throw new \RuntimeException('Unabe to perform action: not logged in');
        }
    }
}
