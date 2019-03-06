<?php

namespace Twitter\Task\Step\Condition;

use PHPUnit\Framework\TestCase;
use Twist\Time\Clock;
use Twist\Twitter\Task\Step\Condition\Limit;

class LimitTest extends TestCase
{
    public function testSatisfyWithoutDelay()
    {
        $clock = $this->prophesize(Clock::class);

        $testedInstance = new Limit($clock->reveal());
        $testedInstance->configure(['limit' => 1]);

        $this->assertEquals(true, $testedInstance->satisfy([]));
        $this->assertEquals(false, $testedInstance->satisfy([]));

        $testedInstance->reset();

        $this->assertEquals(true, $testedInstance->satisfy([]));
        $this->assertEquals(false, $testedInstance->satisfy([]));
    }

    public function testSatisfyWithDelay()
    {
        $date = new \DateTime();
        $flushInterval = new \DateInterval($delay = 'P0Y0DT0H10M');
        $ellapsedInterval = new \DateInterval('P0Y0DT0H1M');

        $clock = $this->prophesize(Clock::class);

        $testedInstance = new Limit($clock->reveal());
        $testedInstance->configure(['limit' => 1, 'delay' => $delay]);

        $clock->getNow()->willReturn($date);

        $this->assertEquals(true, $testedInstance->satisfy([]));
        $this->assertEquals(false, $testedInstance->satisfy([]));

        $clock->getNow()->willReturn($date->add($ellapsedInterval));

        $this->assertEquals(false, $testedInstance->satisfy([]));

        $clock->getNow()->willReturn($date->add($flushInterval));

        $this->assertEquals(true, $testedInstance->satisfy([]));
    }
}
