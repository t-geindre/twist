<?php

namespace Twist\Twitter\Task\Step\Action;

use Twist\Twitter\Task\Step\Condition\ConditionInterface;

class ConditionalAction implements ActionInterface
{
    /** @var ConditionInterface[] */
    private $conditions;

    /** @var ActionInterface */
    private $action;

    public function __construct(array $conditions, ActionInterface $action)
    {
        $this->conditions = $conditions;
        $this->action = $action;
    }

    public function execute(array $subject): ?array
    {
        foreach ($this->conditions as $condition) {
            if (!$condition->satisfy($subject)) {
                return $subject;
            }
        }

        return $this->action->execute($subject);
    }
}
