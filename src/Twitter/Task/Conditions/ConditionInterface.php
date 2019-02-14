<?php

namespace App\Twitter\Task\Conditions;

use App\Twitter\Task\Configurable\ConfigurableInterface;

interface ConditionInterface extends ConfigurableInterface
{
    public function satisfy(array $subject): bool;
}
