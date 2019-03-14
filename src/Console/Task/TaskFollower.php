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

    /** @var bool */
    private $isRunning = false;

    /** @var ProgressBar */
    private $progressBar = null;

    public function __construct(SymfonyStyle $io)
    {
        $this->io = $io;
        $this->progressBar = $this->io->createProgressBar();
    }

    public function start(string $name, int $steps)
    {
        $this->isRunning = true;
        $this->io->writeln(sprintf(' <comment>%s</comment>', $this->formatName($name)));
        $this->progressBar->start($steps);
    }


    public function advance(int $steps = 1)
    {
        if ($this->isRunning) {
            $this->progressBar->advance($steps);
        }
    }

    public function ends()
    {
        if ($this->isRunning) {
            $this->isRunning = false;
            $this->progressBar->clear();
            $this->io->writeln(' ');
        }
    }

    public function setSteps(int $steps)
    {
        if ($this->isRunning) {
            $this->progressBar->setMaxSteps($steps);
        }
    }

    public function hide()
    {
        if ($this->isRunning) {
            $this->progressBar->clear();
        }
    }

    public function show()
    {
        if ($this->isRunning) {
            $this->progressBar->display();
        }
    }

    protected function formatName(string $name): string
    {
        return ucfirst(str_replace('_', ' ', $name));
    }
}
