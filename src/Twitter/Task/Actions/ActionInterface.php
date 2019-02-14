<?php

namespace App\Twitter\Task\Actions;

use App\Twitter\Task\Configurable\ConfigurableInterface;

interface ActionInterface extends ConfigurableInterface
{
    public function execute(array $subject): ?array;
}
