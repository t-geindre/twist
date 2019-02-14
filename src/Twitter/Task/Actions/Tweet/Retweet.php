<?php

namespace App\Twitter\Task\Actions\Tweet;

use App\Twitter\Api\Client;
use App\Twitter\Task\Actions\ActionInterface;
use App\Twitter\Task\Configurable\NotConfigurableTrait;

class Retweet implements ActionInterface
{
    use NotConfigurableTrait;

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
