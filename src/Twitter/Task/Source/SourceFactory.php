<?php

namespace Twist\Twitter\Task\Source;

use Psr\Container\ContainerInterface;
use Twist\Twitter\Task\ServiceFactoryTrait;

class SourceFactory
{
    use ServiceFactoryTrait;

    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create(string $type, array $config = []): SourceInterface
    {
        return $this->buildService($this->container, __NAMESPACE__, $type, $config, 'source');
    }
}
