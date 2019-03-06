<?php

namespace Twist\Time;

class Clock
{
    public function getNow(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
