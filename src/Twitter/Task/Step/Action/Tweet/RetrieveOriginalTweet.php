<?php

namespace Twist\Twitter\Task\Step\Action\Tweet;

use Twist\Twitter\Task\Step\Action\ActionInterface;
use Psr\Log\LoggerInterface;

class RetrieveOriginalTweet implements ActionInterface
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function execute(array $tweet): ?array
    {
        if (!empty($tweet['retweeted_status'])) {
            return $tweet['retweeted_status'];
        }

        return $tweet;
    }
}
