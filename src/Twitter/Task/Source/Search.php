<?php

namespace App\Twitter\Task\Source;

use App\Twitter\Api\Client;
use App\Twitter\Task\ConfigurableInterface;

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
