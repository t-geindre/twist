<?php

namespace App\Twitter\Task\Step\Action\Tweet;

use App\Twitter\Api\Client;
use App\Twitter\Task\Step\Action\ActionInterface;

class Retweet implements ActionInterface
{
    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function execute(array $tweet): ?array
    {
        $this->client->retweetStatus(['id' => $tweet['id_str']]);

        return $tweet;
    }
}