<?php

namespace Twist\Twitter\Task\Step\Action\Tweet;

use Twist\Console\Task\TaskFollower;
use Twist\Twitter\Task\Step\Action\ActionInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Display implements ActionInterface
{
    /** @var SymfonyStyle */
    private $io;

    /** @var TaskFollower */
    private $taskFollower;

    public function __construct(SymfonyStyle $io, TaskFollower $taskFollower)
    {
        $this->io = $io;
        $this->taskFollower = $taskFollower;
    }

    public function execute(array $tweet): ?array
    {
        $this->taskFollower->hide();

        $this->io->block(
            sprintf(
                '%s @%s - %s followers',
                $tweet['user']['name'],
                $tweet['user']['screen_name'],
                $tweet['user']['followers_count']
            ),
            null,
            'bg=green;fg=white;options=bold'
        );
        $this->io->block(html_entity_decode($tweet['full_text'] ?? $tweet['text']));
        $this->io->block(
            sprintf(
                '%s retweets - %s favorites',
                $tweet['retweet_count'],
                $tweet['favorite_count']
            ),
            null,
            'bg=blue;fg=white'
        );

        $this->taskFollower->show();

        return $tweet;
    }
}
