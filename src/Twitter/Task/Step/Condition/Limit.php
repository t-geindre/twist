<?php

namespace Twist\Twitter\Task\Step\Condition;

use Twist\Time\Clock;
use Twist\Twitter\Task\ConfigurableInterface;
use Twist\Twitter\Task\Step\ResetableInterface;

class Limit implements ConditionInterface, ConfigurableInterface, ResetableInterface
{
    /** @var int */
    private $count = 0;

    /** @var int */
    private $limit;

    /** @var \DateTime[] */
    private $expires = [];

    /** @var Clock */
    private $clock;

    /** @var \DateInterval|null */
    private $delay;

    public function __construct(Clock $clock)
    {
        $this->clock = $clock;
    }

    public function satisfy(array $subject): bool
    {
        $this->refresh();

        if ($this->count < $this->limit) {
            if ($this->delay) {
                $this->expires[] = (clone $this->clock->getNow())->add($this->delay);
            }

            $this->count++;

            return true;
        }

        return false;
    }

    public function configure(array $config): void
    {
        if (!empty($config['delay'])) {
            try {
                $this->delay = new \DateInterval($config['delay']);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException('Invalid date interval format', 0, $e);
            }
        }

        if (empty($config['limit'])) {
            throw new \InvalidArgumentException('Invalid limit');
        }

        $this->limit = (int) $config['limit'];
        $this->count = 0;
    }

    public function reset(): void
    {
        if (!$this->delay) {
            $this->count = 0;
        }
    }

    protected function refresh(): void
    {
        if ($this->delay) {
            $this->expires = array_filter(
                $this->expires,
                function (\DateTime $expire) {
                    return $expire > $this->clock->getNow();
                }
            );
            $this->count = count($this->expires);
        }
    }
}
