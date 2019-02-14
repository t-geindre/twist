<?php

namespace App\Twitter\Task\Actions\Tweet;

use App\Twitter\Task\Actions\ActionInterface;
use App\Twitter\Task\Actions\User\Friendship;

class FriendshipOwner implements ActionInterface
{
    /** @var Friendship */
    private $follow;

    public function __construct(Friendship $follow)
    {
        $this->follow = $follow;
    }

    public function configure(?array $config): void
    {
        $this->follow->configure($config);
    }

    public function execute(array $tweet): ?array
    {
        $tweet['user'] = $this->follow->execute($tweet['user']);

        return $tweet;
    }
}
