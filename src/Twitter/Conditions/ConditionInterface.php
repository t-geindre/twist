<?php

namespace App\Twitter\Conditions;

use App\Twitter\Configurable\ConfigurableInterface;

interface ConditionInterface extends ConfigurableInterface
{
    public function satisfy(array $subject): bool;
}
