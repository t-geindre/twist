<?php

namespace Twist\Twitter\Task\Step\Condition;

use Symfony\Component\Console\Style\SymfonyStyle;
use Twist\Console\Task\TaskFollower;
use Twist\Twitter\Task\ConfigurableInterface;
use Twist\Twitter\Task\Step\Condition\Scoring\ScoredCondition;
use Twist\Twitter\Task\Step\ResetableInterface;
use Twist\Twitter\Task\Step\StepFactory;

class Scoring implements ConditionInterface, ConfigurableInterface, ResetableInterface
{
    /** @var ScoredCondition[] */
    protected $conditions;

    /** @var StepFactory */
    protected $stepFactory;

    /** @var float */
    protected $threshold;

    /** @var SymfonyStyle */
    private $io;
    
    /** @var TaskFollower */
    private $taskFollower;

    public function __construct(StepFactory $stepFactory, SymfonyStyle $io, TaskFollower $taskFollower)
    {
        $this->stepFactory = $stepFactory;
        $this->io = $io;
        $this->taskFollower = $taskFollower;
    }

    public function satisfy(array $subject): bool
    {
        $score = .0;

        foreach ($this->conditions as $condition) {
            if ($condition->satisfy($subject)) {
                $score += $condition->getScore();
            }
        }

        return $score >= $this->threshold;
    }

    public function configure(array $config): void
    {
        $conditions = $config['conditions'] ?? [];

        $this->conditions = array_map(
            function (ConditionInterface $condition, float $score) {
                return new ScoredCondition($condition, $score);
            },
            $this->stepFactory->createMultipleConditions($conditions),
            array_column($conditions, 'score')
        );

        if (count($this->conditions) === 0) {
            throw new \InvalidArgumentException('Scoring condition requires at least one valid condition');
        }

        $this->threshold = (float) $config['threshold'] ?? 0;
    }

    public function reset(): void
    {
        array_map(function (ScoredCondition $condition) {
            $condition->reset();
        }, $this->conditions);
    }
}
