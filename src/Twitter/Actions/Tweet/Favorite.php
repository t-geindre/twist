<?php

namespace App\Twitter\Actions\Tweet;

use App\Twitter\Actions\ActionInterface;
use App\Twitter\Api\Client;
use App\Twitter\Configurable\NotConfigurableTrait;

class Favorite implements ActionInterface
{
    use NotConfigurableTrait;

    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function execute(array $tweet): array
    {
        $this->client->createFavorite(['id' => $tweet['id_str']]);

        return $tweet;
    }
}
