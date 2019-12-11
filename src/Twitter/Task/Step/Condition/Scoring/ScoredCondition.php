<?php

namespace Twist\Twitter\Task\Step\Condition\Scoring;

use Twist\Twitter\Task\Step\Condition\ConditionInterface;
use Twist\Twitter\Task\Step\ResetableInterface;

class ScoredCondition implements ConditionInterface, ResetableInterface
{
    /** @var ConditionInterface */
    protected $condition;

    /** @var float */
    protected $score;

    public function __construct(ConditionInterface $condition, float $score)
    {
        $this->condition = $condition;
        $this->score = $score;
    }

    public function satisfy(array $subject): bool
    {
        return $this->condition->satisfy($subject);
    }

    public function getScore(): float
    {
        return $this->score;
    }

    public function reset(): void
    {
        if ($this->condition instanceof ResetableInterface) {
            $this->condition->reset();
        }
    }
}
