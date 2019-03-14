<?php

namespace Twist\Tests\Twitter\Task\Source;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Twist\Twitter\Task\Source\SourceFactory;
use Twist\Twitter\Task\Source\SourceInterface;
use Twist\Twitter\Task\Source\Tweet\Search;

class SourceFactoryTest extends TestCase
{
    public function testCreate()
    {
        $source = $this->prophesize(SourceInterface::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(SourceInterface::class)->willReturn(true)->shouldBeCalledOnce();
        $container->get(SourceInterface::class)->willReturn($source->reveal())->shouldBeCalledOnce();

        $sourceFactory = new SourceFactory($container->reveal());

        $this->assertEquals(
            $source->reveal(),
            $sourceFactory->create('SourceInterface')
        );
    }

    public function testCreateConfigurable()
    {
        $config = ['foo' => 'bar'];

        $source = $this->prophesize(Search::class);
        $source->configure($config)->shouldBeCalledOnce();


        $container = $this->prophesize(ContainerInterface::class);
        $container->has(Search::class)->willReturn(true)->shouldBeCalledOnce();
        $container->get(Search::class)->willReturn($source->reveal())->shouldBeCalledOnce();

        $sourceFactory = new SourceFactory($container->reveal());

        $this->assertEquals(
            $source->reveal(),
            $sourceFactory->create('Tweet\Search', $config)
        );
    }

    public function testCreateFails()
    {
        $this->expectException(\InvalidArgumentException::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(SourceInterface::class)->willReturn(false)->shouldBeCalledOnce();

        $sourceFactory = new SourceFactory($container->reveal());

        $sourceFactory->create('SourceInterface');
    }
}
