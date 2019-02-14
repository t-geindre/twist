<?php

namespace App\Twitter\Task\Source;

use App\Twitter\Api\Client;

class Search implements SourceInterface
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
