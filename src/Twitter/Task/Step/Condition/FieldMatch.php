<?php

namespace Twist\Twitter\Task\Step\Condition;

use Twist\Twitter\Task\ConfigurableInterface;
use Twist\Twitter\Task\FieldResolverTrait;

class FieldMatch implements ConditionInterface, ConfigurableInterface
{
    use FieldResolverTrait;

    /** @var array */
    private $config;

    public function configure(array $config): void
    {
        $this->config = $config;
    }

    public function satisfy(array $subject): bool
    {
        return (bool) preg_match(
            $this->config['pattern'],
            $this->resolveField($this->config['field'], $subject)
        );
    }
}
