<?php

namespace Twist\Twitter\Task;

use Doctrine\ORM\EntityManager;
use Twist\Scheduler\TaskFollowerInterface;
use Twist\Scheduler\TaskInterface;
use Twist\Twitter\Task\Source\SourceInterface;
use Twist\Twitter\Task\Step\Action\ActionInterface;
use Twist\Twitter\Task\Step\Condition\ConditionInterface;
use Twist\Twitter\Task\Step\ResetableInterface;
use Twist\Twitter\Task\Step\StepInterface;

class Task implements TaskInterface
{
    /** @var SourceInterface */
    private $source
    ;
    /** @var StepInterface[] */
    private $steps;

    /** @var int */
    private $pauseDuration;

    /** @var int */
    private $startDelay;

    /** @var TaskFollowerInterface */
    private $taskFollower;

    /** @var string */
    private $name;

    /** @var EntityManager */
    private $em;

    public function __construct(
        TaskFollowerInterface $taskFollower,
        SourceInterface $source,
        EntityManager $em,
        string $name,
        array $steps,
        int $pauseDuration,
        int $startDelay = 0
    ) {
        $this->source = $source;
        $this->steps = $steps;
        $this->pauseDuration = $pauseDuration;
        $this->startDelay = $startDelay;
        $this->taskFollower = $taskFollower;
        $this->name = $name;
        $this->em = $em;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStartDelay(): int
    {
        return $this->startDelay;
    }

    public function getPauseDuration(): int
    {
        return $this->pauseDuration;
    }

    public function run(): void
    {
        foreach ($this->steps as $step) {
            if ($step instanceof ResetableInterface) {
                $step->reset();
            }
        }

        $this->taskFollower->start($this->name, 1);

        $items = $this->source->execute();

        $this->taskFollower->setSteps(count($items));

        foreach ($items as $item) {
            $this->taskFollower->advance();
            foreach ($this->steps as $stepName => $step) {
                if ($step instanceof ConditionInterface) {
                    if (!$step->satisfy($item)) {
                        continue 2;
                    }
                }

                if ($step instanceof ActionInterface) {
                    $item = $step->execute($item);
                    if (null === $item) {
                        continue 2;
                    }
                }
            }
        }

        $this->taskFollower->ends();

        // Since some steps might use EM
        // it has to be cleared after each task
        $this->em->clear();
    }
}
