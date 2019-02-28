<?php

namespace Twist\Scheduler;

use Psr\Log\LoggerInterface;

class Scheduler
{
    /** @var array */
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
        $this->tasks[] = [
            'pause' => $task->getStartDelay(),
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
                try {
                    $task['task']->run();
                } catch(\Throwable $e) {
                    if (!$this->handleException) {
                        throw $e;
                    }

                    $this->logger->error($e->getMessage());
                    $this->logger->warning('Task stopped, next run scheduled');
                }
                $task['pause'] = $task['task']->getPauseDuration();
            }
        }
    }
}
