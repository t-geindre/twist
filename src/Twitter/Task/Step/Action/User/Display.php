<?php

namespace Twist\Twitter\Task\Step\Action\User;

use Symfony\Component\Console\Style\SymfonyStyle;
use Twist\Console\Task\TaskFollower;
use Twist\Twitter\Task\Step\Action\ActionInterface;

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

    public function execute(array $user): ?array
    {
        $this->taskFollower->hide();

        $this->io->block(
            sprintf(
                '%s @%s - %s followers',
                $user['name'],
                $user['screen_name'],
                $user['followers_count']
            ),
            null,
            'bg=green;fg=white;options=bold'
        );

        $this->taskFollower->show();

        return $user;
    }
}
