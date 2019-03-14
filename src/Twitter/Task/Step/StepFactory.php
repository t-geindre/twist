<?php

namespace Twist\Twitter\Task\Step;

use Psr\Container\ContainerInterface;
use Twist\Twitter\Task\ServiceFactoryTrait;
use Twist\Twitter\Task\Step\Action\ActionInterface;
use Twist\Twitter\Task\Step\Action\ConditionalAction;
use Twist\Twitter\Task\Step\Condition\ConditionInterface;

class StepFactory
{
    use ServiceFactoryTrait;

    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create(string $type, array $config = [], array $conditions = []): StepInterface
    {
        $step = $this->buildService($this->container, __NAMESPACE__, $type, $config, 'step');

        if (!empty($conditions)) {
            if (!$step instanceof ActionInterface) {
                throw new \InvalidArgumentException('Only an action can be conditional');
            }

            $step = new ConditionalAction($this->createMultipleConditions($conditions), $step);
        }

        return $step;
    }

    public function createMultiple(array $steps): array
    {
        return array_map(function ($step) {
            return $this->create($step['type'], $step['config'] ?? [], $step['conditions'] ?? []);
        }, $steps);
    }

    public function createCondition(string $type, array $config = []): ConditionInterface
    {
        $condition = $this->create($type, $config);

        if (!$condition instanceof ConditionInterface) {
            throw new \InvalidArgumentException(sprintf('Invalid condition "%s"', $type));
        }

        return $condition;
    }

    public function createMultipleConditions(array $conditions): array
    {
        return array_map(function ($condition) {
            return $this->create($condition['type'], $condition['config'] ?? []);
        }, $conditions);
    }
}
