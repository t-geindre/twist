<?php

namespace Twist\Twitter\Task;

interface ConfigurableInterface
{
    public function configure(array $config): void;
}
