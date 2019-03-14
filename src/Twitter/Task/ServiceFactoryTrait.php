<?php

namespace Twist\Twitter\Task;

use Psr\Container\ContainerInterface;

trait ServiceFactoryTrait
{
    protected function buildService(
        ContainerInterface $container,
        string $namespace,
        string $type,
        array $config,
        string $name
    ) {
        $class = sprintf('%s\%s', $namespace, $type);

        if (!$container->has($class)) {
            throw new \InvalidArgumentException(sprintf('Unknown %s type "%s"', $name, $type));
        }

        $service = $container->get($class);

        if ($service instanceof ConfigurableInterface) {
            $service->configure($config);
        }

        return $service;
    }
}
