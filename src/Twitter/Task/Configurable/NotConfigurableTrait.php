<?php

namespace App\Twitter\Task\Configurable;

trait NotConfigurableTrait
{
    public function configure(?array $config): void
    {
        if (!empty($config)) {
            throw new \InvalidArgumentException('This is not configurable');
        }
    }
}
