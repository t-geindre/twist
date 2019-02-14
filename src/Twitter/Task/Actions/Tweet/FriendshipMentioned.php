<?php

namespace App\Twitter\Task\Actions\Tweet;

use App\Twitter\Task\Actions\User\Friendship;
use App\Twitter\Task\Actions\ActionInterface;

class FriendshipMentioned implements ActionInterface
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
        foreach ($tweet['entities']['user_mentions'] ?? [] as &$user) {
            $user = $this->follow->execute($user);
        }

        return $tweet;
    }
}
