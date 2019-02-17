<?php

namespace App\Twitter\Task\Step\Action\User;

use App\Twitter\Task\Step\Action\ActionInterface;
use App\Twitter\Api\Client;
use App\Twitter\Task\ConfigurableInterface;

class Friendship implements ActionInterface, ConfigurableInterface
{
    /** @var Client */
    private $client;

    /** @var bool  */
    private $follow = false;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function configure(array $config): void
    {
        $this->follow = (bool) ($config['follow'] ?? false);
    }

    public function execute(array $user): ?array
    {
        $this->client->createFriendship(array_filter([
            'user_id' => $user['id_str'],
            'follow' => $this->follow === false ? 'false' : null
        ]));

        return $user;
    }
}