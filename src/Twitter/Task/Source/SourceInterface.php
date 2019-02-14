<?php

namespace App\Twitter\Task\Source;

use App\Twitter\Task\Configurable\ConfigurableInterface;

interface SourceInterface extends ConfigurableInterface
{
    public function execute(): array;
}
