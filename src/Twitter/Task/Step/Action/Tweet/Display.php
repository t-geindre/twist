<?php

namespace Twist\Twitter\Task\Step\Action\Tweet;

use Twist\Console\Task\TaskFollower;
use Twist\Twitter\Task\Step\Action\ActionInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twist\Twitter\Task\Step\Action\User\Display as UserDisplay;

class Display implements ActionInterface
{
    /** @var SymfonyStyle */
    private $io;

    /** @var TaskFollower */
    private $taskFollower;

    /** @var UserDisplay */
    private $userDisplay;

    public function __construct(SymfonyStyle $io, TaskFollower $taskFollower, UserDisplay $userDisplay)
    {
        $this->io = $io;
        $this->taskFollower = $taskFollower;
        $this->userDisplay = $userDisplay;
    }

    public function execute(array $tweet): ?array
    {
        $tweet['user'] = $this->userDisplay->execute($tweet['user']);

        $this->taskFollower->hide();

        $this->io->block(html_entity_decode($tweet['full_text'] ?? $tweet['text']));
        $this->io->block(
            sprintf(
                '%s retweets - %s favorites - %s',
                $tweet['retweet_count'],
                $tweet['favorite_count'],
                (new \DateTime($tweet['created_at']))
                    ->setTimezone(new \DateTimeZone('Europe/Paris'))
                    ->format('d/m/Y H:i:s')
            ),
            null,
            'bg=blue;fg=white'
        );

        $this->taskFollower->show();

        return $tweet;
    }
}
