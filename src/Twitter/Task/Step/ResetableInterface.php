<?php

namespace App\Twitter\Task\Step;

interface ResetableInterface
{
    public function reset(): void;
}
