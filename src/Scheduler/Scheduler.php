<?php

namespace App\Scheduler;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Scheduler
{
    /** @var array */
    private $tasks = [];

    /** @var LoggerInterface */
    private $logger;

    /** @var SymfonyStyle */
    private $io;

    public function __construct(LoggerInterface $logger, SymfonyStyle $io)
    {
        $this->logger = $logger;
        $this->io = $io;
    }

    public function addTask(TaskInterface $task)
    {
        $this->tasks[] = [
            'pause' => $task->startImmediately() ? 0 : $task->getPauseDuration(),
            'task' => $task
        ];
    }

    public function run()
    {
        while (true) {
            $this->runTasks();
            $this->pause();
        }
    }

    protected function pause()
    {
        $duration = min(array_column($this->tasks, 'pause'));

        $this->logger->info(sprintf('%d second(s) pause before executing next task', $duration));

        $progress = $this->io->createProgressBar();
        $progress->setMaxSteps($duration);
        $progress->setMessage('Pause before next search');
        for ($i = 0; $i < $duration; $i++) {
            sleep(1);
            $progress->advance();
        }
        $progress->clear();

        foreach ($this->tasks as &$task) {
            $task['pause'] -= $duration;
        }
    }

    protected function runTasks()
    {
        if (count($this->tasks) === 0) {
            throw new \RuntimeException('There is no task to run');
        }

        foreach ($this->tasks as &$task) {
            if ($task['pause'] === 0) {
                $task['task']->run();
                $task['pause']= $task['task']->getPauseDuration();
            }
        }
    }
}
