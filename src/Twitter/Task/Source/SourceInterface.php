<?php

namespace App\Twitter\Task\Source;

interface SourceInterface
{
    public function execute(): array;
}
