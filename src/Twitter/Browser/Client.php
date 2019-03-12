<?php

namespace Twist\Twitter\Browser;

use HeadlessChromium\Browser;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Exception\JavascriptException;
use HeadlessChromium\Exception\OperationTimedOut;
use HeadlessChromium\Page;
use HeadlessChromium\PageUtils\PageEvaluation;
use Psr\Log\LoggerInterface;
use Twist\Twitter\Browser\Exception\RequestException;

class Client
{
    const LOGIN_URL = 'https://twitter.com/login';
    const IDLE_URL = 'https://twitter.com/search-home';
    const REQUEST_POLLING_PAUSE = '100'; // ms

    /** @var array|null */
    private $requestHeaders;

    /** @var bool */
    private $loggedIn = false;

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $headersInterceptorScript;

    /** @var string */
    private $requestProxyScript;

    /** @var array */
    private $browserOptions;

    /** @var BrowserFactory */
    private $browserFactory;

    /** @var Browser */
    private $browser;

    /** @var Page */
    private $page;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    public function __construct(
        BrowserFactory $browserFactory,
        LoggerInterface $logger,
        string $headersInterceptorScript,
        string $requestProxyScript,
        array $browserOptions
    ) {
        $this->logger = $logger;
        $this->headersInterceptorScript = $headersInterceptorScript;
        $this->requestProxyScript = $requestProxyScript;
        $this->browserFactory = $browserFactory;
        $this->browserOptions = $browserOptions;
    }

    public function login(string $username, string $password): bool
    {
        $this->start();

        $this->username = $username;
        $this->password = $password;

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
        $this->page->navigate(self::IDLE_URL)->waitForNavigation();
        sleep(1); // Make sure the page is fully loaded todo fix this

        return $this->loggedIn = true;
    }

    public function request(array $settings, int $timeout = 3, bool $handleException = true)
    {
        $this->assertLoggedIn();

        $settings = array_merge_recursive($settings, [
            'headers' => array_merge($this->getRequestHeaders() ?? [], ['x-csrf-token' => $this->getCsrfToken()]),
            'xhrFields' => ['withCredentials' => true]
        ]);

        return $this->doRequest($settings, $timeout, $handleException)['data'] ?? [];
    }

    protected function doRequest(array $settings, int $timeout, bool $handleException)
    {
        $uid = uniqid();
        $this->evaluate('twist.sendRequest('.json_encode($uid).', '.json_encode($settings).')');

        $result = null;
        $ellapsedTime = 0;

        for (;;) {
            try {
                $result = $this->evaluate('twist.getRequestResult('.json_encode($uid).')')->getReturnValue();
            } catch (OperationTimedOut $e) {
                if (!$handleException) {
                    throw $e;
                }
                $this->start(true);

                return $this->doRequest($settings, $timeout, false);
            }

            if ($result['status'] === 'pending') {
                usleep(self::REQUEST_POLLING_PAUSE * 1000);

                $ellapsedTime += self::REQUEST_POLLING_PAUSE;
                if (($ellapsedTime / 1000) >= $timeout) {
                    throw new RequestException(sprintf(
                        'An error occurred while requesting "%s", timeout reached',
                        $settings['url'] ?? 'none'
                    ));
                }

                continue;
            }

            break;
        }

        if (($result['status'] ?? 'failed') === 'failed') {
            throw new RequestException(sprintf(
                'An error occurred while requesting "%s", status: "%s"',
                $settings['url'] ?? 'none',
                $code = $result['code'] ?? 0
            ), $code);
        }

        return $result;
    }

    public function evaluate(string $script): PageEvaluation
    {
        $this->start();

        return $this->page->evaluate($script);
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

        $this->requestHeaders = null;

        do {
            // Scroll down until there's an API call
            $this->evaluate("window.scrollTo(0,document.body.scrollHeight);");
            usleep(100000); // 100ms
            $this->requestHeaders = $this->evaluate('twist.getInterceptedHeaders()')->getReturnValue();
        } while (false === $this->requestHeaders);
    }

    protected function assertLoggedIn(): void
    {
        if (!$this->loggedIn) {
            throw new \RuntimeException('Unabe to perform action: not logged in');
        }
    }

    protected function start(bool $restart = false)
    {
        if (null !== $this->browser) {
            if (false === $restart) {
                return;
            }
            $this->logger->info('Closing browser');
            $this->browser->close();
        }

        $this->logger->info('Opening new browser');
        $this->browser = $this->browserFactory->createBrowser($this->browserOptions);

        $this->page = $this->browser->createPage();
        $this->page->addPreScript(
            file_get_contents($this->headersInterceptorScript)."\n".
            file_get_contents($this->requestProxyScript)
        );

        if ($this->loggedIn) {
            $this->loggedIn = false;
            $this->login($this->username, $this->password);
        }
    }
}
