<?php

namespace Twist\Twitter\Task;

use Twist\Scheduler\TaskFollowerInterface;
use Twist\Twitter\Task\Source\SourceInterface;
use Twist\Twitter\Task\Step\Action\ActionInterface;
use Twist\Twitter\Task\Step\Action\ConditionalAction;
use Twist\Twitter\Task\Step\Condition\ConditionInterface;
use Twist\Twitter\Task\Step\StepInterface;
use Psr\Container\ContainerInterface;

class TaskFactory
{
    const SOURCE_NAMESPACE = 'Twist\Twitter\Task\Source\\';
    const STEP_NAMESPACE = 'Twist\Twitter\Task\Step\\';

    /** @var ContainerInterface */
    private $container;

    /** @var TaskFollowerInterface */
    private $taskFollower;

    public function __construct(ContainerInterface $container, TaskFollowerInterface $taskFollower)
    {
        $this->container = $container;
        $this->taskFollower = $taskFollower;
    }

    public function create(array $config): Task
    {
        return new Task(
            $this->taskFollower,
            $this->getSource(
                $config['source']['type'],
                $config['source']['config'] ?? []
            ),
            $config['name'],
            $this->getSteps(
                $config['steps']
            ),
            (int) ($config['pause'] ?? 0),
            (int) ($config['start_delay'] ?? 0)
        );
    }

    public function getSteps(array $steps): array
    {
        $loadedSteps = [];
        foreach ($steps as $stepName => $step) {
            $loadedStep = $this->getStep($step['type'], $step['config'] ?? []);
            if (!empty($step['conditions']) && $loadedStep instanceof ActionInterface) {
                $loadedStep = new ConditionalAction(
                    $this->getConditions($step['conditions']),
                    $loadedStep
                );
            }
            $loadedSteps[$stepName] = $loadedStep;
        }

        return $loadedSteps;
    }

    public function getConditions(array $conditions): array
    {
        $loadedConditions = [];
        foreach ($conditions as $condition) {
            $loadedConditions[] = $this->getCondition($condition['type'], $condition['config'] ?? []);
        }

        return $loadedConditions;
    }

    public function getCondition(string $type, array $config): ConditionInterface
    {
        $condition = $this->getStep($type, $config);

        if (!$condition instanceof ConditionInterface) {
            throw new \InvalidArgumentException(sprintf("%s cannot be used as an action condition", $type));
        }

        return $condition;
    }

    public function getStep(string $type, array $config): StepInterface
    {
        $class = self::STEP_NAMESPACE.$type;

        if (!$this->container->has($class)) {
            throw new \InvalidArgumentException(sprintf('Unknown step type "%s"', $type));
        }

        $step = $this->container->get($class);

        if ($step instanceof ConfigurableInterface) {
            $step->configure($config);
        }

        return $step;
    }

    public function getSource(string $type, array $config): SourceInterface
    {
        $class = self::SOURCE_NAMESPACE.$type;

        if (!$this->container->has($class)) {
            throw new \InvalidArgumentException(sprintf('Unknown source type "%s"', $type));
        }

        $source = $this->container->get($class);

        if ($source instanceof ConfigurableInterface) {
            $source->configure($config);
        }

        return $source;
    }
}
