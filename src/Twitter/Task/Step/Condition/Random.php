<?php

namespace Twist\Twitter\Task\Step\Condition;

use Twist\Twitter\Task\ConfigurableInterface;

class Random implements ConditionInterface, ConfigurableInterface
{
    /** @var int */
    private $percentage;

    public function satisfy(array $subject): bool
    {
        return mt_rand(0, 100) <= $this->percentage;
    }

    public function configure(array $config): void
    {
        if (empty($config['percentage']) || !is_numeric($config['percentage']) || $config['percentage'] < 1 || $config['percentage'] > 99) {
            throw new \InvalidArgumentException('Invalid configuration, "percentage" missing or invalid');
        }

        $this->percentage = (int) $config['percentage'];
    }
}
