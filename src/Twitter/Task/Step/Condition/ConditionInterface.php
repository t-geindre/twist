<?php

namespace App\Twitter\Task\Step\Condition;

use App\Twitter\Task\Step\StepInterface;

interface ConditionInterface extends StepInterface
{
    public function satisfy(array $subject): bool;
}
