<?php

namespace App\Twitter\Actions\Tweet;

use App\Twitter\Actions\ActionInterface;
use App\Twitter\Actions\User\Friendship;

class FrienshipMentioned implements ActionInterface
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

    public function execute(array $tweet): array
    {
        foreach ($tweet['entities']['user_mentions'] ?? [] as &$user) {
            $user = $this->follow->execute($user);
        }

        return $tweet;
    }
}
