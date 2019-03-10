<?php

namespace Twist\Console\Task;

use Twist\Scheduler\TaskFollowerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;

class TaskFollower implements TaskFollowerInterface
{
    const PROGRESSBAR_FORMAT = 'taskfollower_format';
    /** @var SymfonyStyle */
    private $io;

    /** @var ?ProgressBar */
    private $progressBar = null;

    public function __construct(SymfonyStyle $io)
    {
        $this->io = $io;
    }

    public function start(string $name, int $steps)
    {
        $this->io->writeln(sprintf(' <comment>%s</comment>', $this->formatName($name)));
        $this->progressBar = $this->io->createProgressBar($steps);
        $this->progressBar->start();
    }


    public function advance(int $steps = 1)
    {
        if (null !== $this->progressBar) {
            $this->progressBar->advance($steps);
        }
    }

    public function ends()
    {
        if (null !== $this->progressBar) {
            $this->progressBar->clear();
            $this->io->writeln(' ');

            $this->progressBar = null;
        }
    }

    public function setSteps(int $steps)
    {
        if (null !== $this->progressBar) {
            $this->progressBar->setMaxSteps($steps);
        }
    }

    public function hide()
    {
        if (null !== $this->progressBar) {
            $this->progressBar->clear();
        }
    }

    public function show()
    {
        if (null !== $this->progressBar) {
            $this->progressBar->display();
        }
    }

    protected function formatName(string $name): string
    {
        return ucfirst(str_replace('_', ' ', $name));
    }
}
