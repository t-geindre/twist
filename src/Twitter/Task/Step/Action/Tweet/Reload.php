<?php

namespace Twist\Twitter\Task\Step\Action\Tweet;

use Twist\Twitter\Api\Client;
use Twist\Twitter\Browser\Exception\RequestException;
use Twist\Twitter\Task\Step\Action\ActionInterface;
use Twist\Twitter\Task\ConfigurableInterface;

class Reload implements ActionInterface, ConfigurableInterface
{
    /** @var Client */
    private $client;

    /** @var array */
    private $config;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function execute(array $tweet): ?array
    {
        $status = null;

        try {
            $status = $this->client->getStatus(array_merge(
                ['id' => $tweet['id_str']],
                $this->config
            ));
        } catch (RequestException $e) {
            if (!in_array($e->getCode(), [
                '404', // Tweet removed
                '401', // Blocked by tweet owner
            ])) {
                throw $e;
            }
        }

        return $status;
    }

    public function configure(array $config): void
    {
        $this->config = $config;
    }
}
