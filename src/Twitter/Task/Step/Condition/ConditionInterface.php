<?php

namespace Twist\Twitter\Task\Step\Condition;

use Twist\Twitter\Task\Step\StepInterface;

interface ConditionInterface extends StepInterface
{
    public function satisfy(array $subject): bool;
}
