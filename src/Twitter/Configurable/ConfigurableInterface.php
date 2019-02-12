<?php

namespace App\Twitter\Configurable;

interface ConfigurableInterface
{
    public function configure(?array $config): void;
}
