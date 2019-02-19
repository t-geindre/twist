<?php

namespace Twist\Twitter\Task\Step\Action\Tweet;

use Twist\Twitter\Api\Client;
use Twist\Twitter\Task\Step\Action\ActionInterface;

class Favorite implements ActionInterface
{
    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function execute(array $tweet): ?array
    {
        $this->client->createFavorite(['id' => $tweet['id_str']]);

        $tweet['favorited'] = true;

        return $tweet;
    }
}
