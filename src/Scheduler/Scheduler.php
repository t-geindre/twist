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

    /** @var TaskFollowerInterface */
    private $taskFollower;

    public function __construct(LoggerInterface $logger, TaskFollowerInterface $taskFollower)
    {
        $this->logger = $logger;
        $this->taskFollower = $taskFollower;
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

        $this->taskFollower->start(
            sprintf('%d second(s) pause before executing next task', $duration),
            $duration
        );

        for ($i = 0; $i < $duration; $i++) {
            sleep(1);
            $this->taskFollower->advance();
        }

        $this->taskFollower->ends();

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
                $task['pause'] = $task['task']->getPauseDuration();
            }
        }
    }
}
