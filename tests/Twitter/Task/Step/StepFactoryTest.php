<?php

namespace Twist\Tests\Twitter\Task\Step;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;
use Twist\Twitter\Task\Step\Action\ActionInterface;
use Twist\Twitter\Task\Step\Action\ConditionalAction;
use Twist\Twitter\Task\Step\Condition\Limit;
use Twist\Twitter\Task\Step\StepFactory;
use Twist\Twitter\Task\Step\StepInterface;

class StepFactoryTest extends TestCase
{
    public function testCreate()
    {
        $step = $this->prophesize(StepInterface::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(StepInterface::class)->willReturn(true)->shouldBeCalledOnce();
        $container->get(StepInterface::class)->willReturn($step->reveal())->shouldBeCalledOnce();

        $stepFactory = new StepFactory($container->reveal());

        $this->assertEquals(
            $step->reveal(),
            $stepFactory->create('StepInterface')
        );
    }

    public function testCreateFails()
    {
        $this->expectException(\InvalidArgumentException::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(Argument::any())->willReturn(false)->shouldBeCalledOnce();

        $stepFactory = new StepFactory($container->reveal());
        $stepFactory->create('foo');
    }

    public function testCreateConfigurable()
    {
        $config = ['foo' => 'bar'];

        $step = $this->prophesize(Limit::class);
        $step->configure($config)->shouldBeCalledOnce();

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(Limit::class)->willReturn(true)->shouldBeCalledOnce();
        $container->get(Limit::class)->willReturn($step->reveal())->shouldBeCalledOnce();

        $stepFactory = new StepFactory($container->reveal());
        $this->assertEquals(
            $step->reveal(),
            $stepFactory->create('Condition\Limit', $config)
        );
    }

    public function testCreateConditional()
    {
        $step = $this->prophesize(ActionInterface::class);

        $condition = $this->prophesize(Limit::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ActionInterface::class)->willReturn(true)->shouldBeCalledOnce();
        $container->get(ActionInterface::class)->willReturn($step->reveal())->shouldBeCalledOnce();
        $container->has(Limit::class)->willReturn(true)->shouldBeCalledOnce();
        $container->get(Limit::class)->willReturn($condition->reveal())->shouldBeCalledOnce();

        $stepFactory = new StepFactory($container->reveal());

        $this->assertInstanceOf(
            ConditionalAction::class,
            $stepFactory->create(
                'Action\ActionInterface',
                [],
                [['type' => 'Condition\Limit', 'config' => ['foo' => 'bar']]]
            )
        );
    }

    public function testCreateConditionalFails()
    {
        $this->expectException(\InvalidArgumentException::class);

        $step = $this->prophesize(StepInterface::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(StepInterface::class)->willReturn(true)->shouldBeCalledOnce();
        $container->get(StepInterface::class)->willReturn($step->reveal())->shouldBeCalledOnce();

        $stepFactory = new StepFactory($container->reveal());

        $this->assertEquals(
            $step->reveal(),
            $stepFactory->create(
                'StepInterface',
                [],
                [['type' => 'Condition\Limit', 'config' => ['foo' => 'bar']]]
            )
        );
    }

    public function testCreateConditionFails()
    {
        $this->expectException(\InvalidArgumentException::class);

        $action = $this->prophesize(ActionInterface::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ActionInterface::class)->willReturn(true)->shouldBeCalledOnce();
        $container->get(ActionInterface::class)->willReturn($action->reveal())->shouldBeCalledOnce();

        $stepFactory = new StepFactory($container->reveal());
        $stepFactory->createCondition('Action\ActionInterface');
    }
}
