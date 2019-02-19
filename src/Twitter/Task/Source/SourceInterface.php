<?php

namespace Twist\Twitter\Task\Source;

interface SourceInterface
{
    public function execute(): array;
}
