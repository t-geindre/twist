<?php

namespace Twist\Time;

class Clock
{
    public function getNow(): \DateTime
    {
        return new \DateTimeImmutable();
    }
}
