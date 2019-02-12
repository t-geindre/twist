<?php

namespace App\Scheduler;

interface TaskInterface
{
    public function startImmediately(): bool;
    public function getPauseDuration(): int; // sec
    public function run(): void;
}
