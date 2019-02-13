<?php

namespace App\Twitter\Task\Configurable;

interface ConfigurableInterface
{
    public function configure(?array $config): void;
}
