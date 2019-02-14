<?php

namespace App\Twitter\Task\Actions\Tweet;

use App\Twitter\Task\Actions\ActionInterface;
use App\Twitter\Task\Configurable\NotConfigurableTrait;
use Psr\Log\LoggerInterface;

class RetrieveOriginalTweet implements ActionInterface
{
    use NotConfigurableTrait;

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
