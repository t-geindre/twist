<?php

namespace Twist\Scheduler;

interface TaskInterface
{
    public function startImmediately(): bool;
    public function getPauseDuration(): int; // sec
    public function run(): void;
}
