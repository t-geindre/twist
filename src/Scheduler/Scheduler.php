<?php

namespace App\Scheduler;

class Scheduler
{
    /** @var array */
    private $tasks = [];

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

        sleep($duration);

        foreach ($this->tasks as &$task) {
            $task['pause'] -= $duration;
        }
    }

    protected function runTasks()
    {
        foreach ($this->tasks as &$task) {
            if ($task['pause'] === 0) {
                $task['task']->run();
                $task['pause']= $task['task']->getPauseDuration();
            }
        }
    }
}
