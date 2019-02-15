<?php

namespace App\Twitter\Task\Actions\Tweet;

use App\Twitter\Api\Client;
use App\Twitter\Task\Actions\ActionInterface;

class Reload implements ActionInterface
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
        return $this->client->getStatus(array_merge(
            ['id' => $tweet['id_str']],
            $this->config
        ));
    }

    public function configure(?array $config): void
    {
        $this->config = $config;
    }
}
