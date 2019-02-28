<?php

namespace Twist\Scheduler;

interface TaskInterface
{
    public function getStartDelay(): int; // sec
    public function getPauseDuration(): int; // sec
    public function run(): void;
}
