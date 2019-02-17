<?php

namespace App\Twitter\Task;

interface ConfigurableInterface
{
    public function configure(array $config): void;
}
