<?php

namespace App\Twitter\Task\Step\Action;

use App\Twitter\Task\Step\StepInterface;

interface ActionInterface extends StepInterface
{
    const EXTRA_FIELDS_NAMESPACE = 'extra_bot_contest';

    public function execute(array $subject): ?array;
}
