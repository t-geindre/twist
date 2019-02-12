<?php

namespace App\Twitter\Actions;

use App\Twitter\Configurable\ConfigurableInterface;

interface ActionInterface extends ConfigurableInterface
{
    public function execute(array $subject): array;
}
