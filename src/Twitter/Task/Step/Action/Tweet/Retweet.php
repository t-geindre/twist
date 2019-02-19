<?php

namespace Twist\Twitter\Task\Step\Action\Tweet;

use Twist\Twitter\Api\Client;
use Twist\Twitter\Task\Step\Action\ActionInterface;

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

        $tweet['retweeted'] = true;

        return $tweet;
    }
}
