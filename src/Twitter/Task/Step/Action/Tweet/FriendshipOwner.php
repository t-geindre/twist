<?php

namespace App\Twitter\Task\Step\Action\Tweet;

use App\Twitter\Task\Step\Action\ActionInterface;
use App\Twitter\Task\Step\Action\User\Friendship;
use App\Twitter\Task\ConfigurableInterface;

class FriendshipOwner implements ActionInterface, ConfigurableInterface
{
    /** @var Friendship */
    private $follow;

    public function __construct(Friendship $follow)
    {
        $this->follow = $follow;
    }

    public function configure(array $config): void
    {
        $this->follow->configure($config);
    }

    public function execute(array $tweet): ?array
    {
        $tweet['user'] = $this->follow->execute($tweet['user']);

        return $tweet;
    }
}
