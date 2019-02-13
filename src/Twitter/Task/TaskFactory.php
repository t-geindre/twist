<?php

namespace App\Twitter\Task;

use Psr\Container\ContainerInterface;

class TaskFactory
{
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create(array $config): Task
    {
        /** @var Task $task */
        $task = $this->container->get(Task::class);
        $task->configure($config);

        return $task;
    }
}
