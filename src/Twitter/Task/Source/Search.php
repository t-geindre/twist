<?php

namespace Twist\Twitter\Task\Source;

use Twist\Twitter\Api\Client;
use Twist\Twitter\Task\ConfigurableInterface;

class Search implements SourceInterface, ConfigurableInterface
{
    /** @var Client */
    private $client;

    /** @var array */
    private $config;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function configure(?array $config): void
    {
        $this->config = null === $config ? [] : $config;
    }

    public function execute(): array
    {
        return $this->client->searchTweets($this->config)['statuses'] ?? [];
    }
}
