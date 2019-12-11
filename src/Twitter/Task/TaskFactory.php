<?php

namespace Twist\Twitter\Task;

use Doctrine\ORM\EntityManager;
use Twist\Scheduler\TaskFollowerInterface;
use Twist\Twitter\Task\Source\SourceFactory;
use Twist\Twitter\Task\Step\StepFactory;

class TaskFactory
{
    const SOURCE_NAMESPACE = 'Twist\Twitter\Task\Source\\';
    const STEP_NAMESPACE = 'Twist\Twitter\Task\Step\\';

    /** @var SourceFactory */
    private $sourceFactory;

    /** @var StepFactory */
    private $stepFactory;

    /** @var TaskFollowerInterface */
    private $taskFollower;

    /** @var EntityManager */
    private $em;

    public function __construct(
        SourceFactory $sourceFactory,
        StepFactory $stepFactory,
        TaskFollowerInterface $taskFollower,
        EntityManager $em
    ) {
        $this->taskFollower = $taskFollower;
        $this->em = $em;
        $this->sourceFactory = $sourceFactory;
        $this->stepFactory = $stepFactory;
    }

    public function create(array $config): Task
    {
        return new Task(
            $this->taskFollower,
            $this->sourceFactory->create(
                $config['source']['type'],
                $config['source']['config'] ?? []
            ),
            $this->em,
            $config['name'],
            $this->stepFactory->createMultiple($config['steps']),
            (int) ($config['pause'] ?? 0),
            (int) ($config['start_delay'] ?? 0),
            (bool) ($config['login_required'] ?? true)
        );
    }
}
