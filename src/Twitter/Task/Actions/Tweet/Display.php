<?php

namespace App\Twitter\Task\Actions\Tweet;

use App\Twitter\Task\Actions\ActionInterface;
use App\Twitter\Task\Configurable\NotConfigurableTrait;
use Symfony\Component\Console\Style\SymfonyStyle;

class Display implements ActionInterface
{
    use NotConfigurableTrait;

    /** @var SymfonyStyle */
    private $io;

    public function __construct(SymfonyStyle $io)
    {
        $this->io = $io;
    }

    public function execute(array $tweet): ?array
    {
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
        $this->io->block($tweet['full_text'] ?? $tweet['text']);
        $this->io->block(
            sprintf(
                '%s retweets - %s favorites',
                $tweet['retweet_count'],
                $tweet['favorite_count']
            ),
            null,
            'bg=blue;fg=white'
        );

        return $tweet;
    }
}
