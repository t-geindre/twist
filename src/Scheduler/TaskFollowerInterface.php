<?php

namespace Twist\Scheduler;

interface TaskFollowerInterface
{
    public function start(string $name, int $steps);
    public function advance(int $steps = 1);
    public function ends();
    public function setSteps(int $steps);
}
