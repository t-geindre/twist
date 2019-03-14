<?php

namespace Twist\Scheduler;

use Psr\Log\LoggerInterface;

class Scheduler
{
    /** @var TaskHandler[] */
    private $tasks = [];

    /** @var LoggerInterface */
    private $logger;

    /** @var TaskFollowerInterface */
    private $taskFollower;

    /** @var bool */
    private $handleException;

    public function __construct(LoggerInterface $logger, TaskFollowerInterface $taskFollower, bool $handleException = true)
    {
        $this->logger = $logger;
        $this->taskFollower = $taskFollower;
        $this->handleException = $handleException;
    }

    public function addTask(TaskInterface $task)
    {
        $this->tasks[] = new TaskHandler($task);
    }

    public function run()
    {
        if (count($this->tasks) === 0) {
            throw new \RuntimeException('There is no task to run');
        }

        while (true) {
            $this->doRun();
            $this->pause();
        }
    }

    protected function pause()
    {
        $duration = min(array_map(
            function (TaskHandler $task) {
                return $task->getRemainingPause();
            },
            $this->tasks
        ));

        $this->taskFollower->start(
            sprintf('%d second(s) pause before executing next task', $duration),
            $duration
        );

        for ($i = 0; $i < $duration; $i++) {
            sleep(1);
            $this->taskFollower->advance();
        }

        $this->taskFollower->ends();

        foreach ($this->tasks as $task) {
            $task->decreasePause($duration);
        }
    }

    protected function doRun()
    {
        foreach ($this->tasks as $task) {
            if ($task->getRemainingPause() === 0) {
                try {
                    $task->run();
                } catch (\Throwable $e) {
                    if (!$this->handleException) {
                        throw $e;
                    }

                    $this->logger->error($e->getMessage());
                    $this->logger->warning('Task stopped, next run scheduled');
                }
            }
        }
    }
}
