<?php

namespace App\Twitter\Task\Step\Condition;

use App\Twitter\Task\ConfigurableInterface;

class FieldMatch implements ConditionInterface, ConfigurableInterface
{
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
            $subject[$this->config['field']]
        );
    }
}
