<?php

namespace App\Twitter\Task\Conditions;

use App\Twitter\Task\Configurable\NotConfigurableTrait;

class IsUnique implements ConditionInterface
{
    Use NotConfigurableTrait {
        configure as parentConfigure;
    }

    /** @var array */
    private $usedIds = [];

    public function satisfy(array $subject): bool
    {
        if (!in_array($subject['id_str'], $this->usedIds)) {
            $this->usedIds[] = $subject['id_str'];
            return true;
        }

        return false;
    }

    public function configure(?array $config): void
    {
        $this->usedIds = [];
        $this->parentConfigure($config);
    }
}
