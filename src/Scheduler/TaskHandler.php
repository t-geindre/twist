<?php

namespace Twist\Scheduler;

class TaskHandler
{
    /** @var int */
    private $remainingPause;

    /** @var TaskInterface */
    private $task;

    public function __construct(TaskInterface $task)
    {
        $this->task = $task;

        $this->remainingPause = $this->task->getStartDelay();
    }

    public function run(): void
    {
        $this->task->run();

        $this->remainingPause = $this->task->getPauseDuration();
    }

    public function getRemainingPause(): int
    {
        return $this->remainingPause;
    }

    public function decreasePause(int $amount = 1): void
    {
        $this->remainingPause -= $amount;
    }
}
