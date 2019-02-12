<?php

namespace App\Twitter\Configurable;

trait NotConfigurableTrait
{
    public function configure(?array $config): void
    {
        if (null !== $config) {
            throw new \InvalidArgumentException('This is not configurable');
        }
    }
}
