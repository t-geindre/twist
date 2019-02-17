<?php

namespace App\Twitter\Task\Step\Action;

use App\Twitter\Task\Step\StepInterface;

interface ActionInterface extends StepInterface
{
    public function execute(array $subject): ?array;
}
