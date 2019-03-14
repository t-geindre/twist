<?php

namespace Twist\Tests\Twitter\Task;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Twist\Scheduler\TaskFollowerInterface;
use Twist\Twitter\Task\Source\SourceFactory;
use Twist\Twitter\Task\Source\SourceInterface;
use Twist\Twitter\Task\Step\StepFactory;
use Twist\Twitter\Task\Task;
use Twist\Twitter\Task\TaskFactory;

class TaskFactoryTest extends TestCase
{
    public function testCreate()
    {
        $sourceFactory = $this->prophesize(SourceFactory::class);
        $sourceFactory
            ->create($source = 'foo', [])
            ->willReturn(
                $this->prophesize(SourceInterface::class)->reveal()
            )
            ->shouldBeCalledOnce();

        $stepFactory = $this->prophesize(StepFactory::class);
        $stepFactory
            ->createMultiple($steps = [['type' => 'foo']])
            ->willReturn([])
            ->shouldBeCalledOnce();

        $taskFactory = new TaskFactory(
            $sourceFactory->reveal(),
            $stepFactory->reveal(),
            $this->prophesize(TaskFollowerInterface::class)->reveal(),
            $this->prophesize(EntityManager::class)->reveal()
        );

        $task = $taskFactory->create([
            'source' => ['type' => $source],
            'steps' => $steps,
            'name' => 'bar',
            'pause' => 100,
            'start_delay' => 50,
        ]);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals('bar', $task->getName());
        $this->assertEquals(100, $task->getPauseDuration());
        $this->assertEquals(50, $task->getStartDelay());
    }
}
