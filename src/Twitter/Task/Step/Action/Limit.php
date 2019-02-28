<?php

namespace Twist\Twitter\Task\Step\Action;

use Twist\Twitter\Task\ConfigurableInterface;
use Twist\Twitter\Task\Step\ResetableInterface;

class Limit implements ActionInterface, ConfigurableInterface, ResetableInterface
{
    /** @var int */
    private $limit;

    /** @var int  */
    private $count = 0;

    public function execute(array $subject): ?array
    {
        if ($this->count++ === $this->limit) {
            return null;
        }

        return $subject;
    }

    public function configure(array $config): void
    {
        if (empty($config['limit']) || !is_numeric($config['limit'])) {
            throw new \InvalidArgumentException('Missing or invalid limit');
        }

        $this->limit = (int) $config['limit'];
    }

    public function reset(): void
    {
        $this->count = 0;
    }
}
