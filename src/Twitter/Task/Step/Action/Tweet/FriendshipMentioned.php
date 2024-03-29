<?php

namespace Twist\Twitter\Task\Step\Action\Tweet;

use Twist\Twitter\Task\Step\Action\ActionInterface;
use Twist\Twitter\Task\Step\Action\User\Friendship;
use Twist\Twitter\Task\ConfigurableInterface;

class FriendshipMentioned implements ActionInterface, ConfigurableInterface
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
        foreach ($tweet['entities']['user_mentions'] ?? [] as &$user) {
            $user = $this->follow->execute($user);
        }

        return $tweet;
    }
}
