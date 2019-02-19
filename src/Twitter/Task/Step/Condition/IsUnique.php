<?php

namespace Twist\Twitter\Task\Step\Condition;

use Twist\Twitter\Task\Step\ResetableInterface;

class IsUnique implements ConditionInterface, ResetableInterface
{
    /** @var array */
    private $usedIds = [];

    public function satisfy(array $subject): bool
    {
        if (!in_array($subject['id_str'], $this->usedIds)) {
            $this->usedIds[] = $subject['id_str'];
            return true;
        }

        return false;
    }

    public function reset(): void
    {
        $this->usedIds = [];
    }
}
