<?php

namespace App\Console\Task;

use App\Scheduler\TaskFollowerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;

class TaskFollower implements TaskFollowerInterface
{
    const PROGRESSBAR_FORMAT = 'taskfollower_format';
    /** @var SymfonyStyle */
    private $io;

    /** @var ProgressBar */
    private $progressBar;

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
        $this->progressBar->advance($steps);
    }

    public function ends()
    {
        $this->progressBar->clear();
        $this->io->writeln(' ');
    }

    public function setSteps(int $steps)
    {
        $this->progressBar->setMaxSteps($steps);
    }

    public function hide()
    {
        $this->progressBar->clear();
    }

    public function show()
    {
        $this->progressBar->display();
    }

    protected function formatName(string $name): string
    {
        return ucfirst(str_replace('_', ' ', $name));
    }
}
