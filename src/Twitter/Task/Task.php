<?php

namespace App\Twitter\Task;

use App\Scheduler\TaskInterface;
use App\Twitter\Task\Actions\ActionInterface;
use App\Twitter\Task\Conditions\ConditionInterface;
use App\Twitter\Task\Configurable\ConfigurableInterface;
use App\Twitter\Task\Source\SourceInterface;
use Psr\Log\LoggerInterface;

class Task implements TaskInterface, ConfigurableInterface
{
    /** @var array */
    private $config;

    /** @var SourceInterface[] */
    private $sources;

    /** @var ConditionInterface[] */
    private $conditions;

    /** @var ActionInterface[] */
    private $actions;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(array $sources, array $conditions, array $actions, LoggerInterface $logger)
    {
        $this->sources = $sources;
        $this->conditions = $conditions;
        $this->actions = $actions;
        $this->logger = $logger;
    }

    public function startImmediately(): bool
    {
        $this->assertIsConfigured();

        return (bool) ($this->config['immediate_start'] ?? true);
    }

    public function getPauseDuration(): int
    {
        $this->assertIsConfigured();

        return (int) $this->config['pause'] ?? 0;
    }

    public function run(): void
    {
        $this->assertIsConfigured();

        $this->logger->info(sprintf('Executing "%s" task', $this->config['name']));

        $data = [];

        foreach ($this->config['steps'] as $step) {
            switch($step['type']) {
                case 'source':
                    $data = $this->executeSource($step);
                    break;
                case 'filter':
                    $data = $this->executeFilter($step, $data);
                    break;
                case 'action':
                    $data = $this->executeAction($step, $data);
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('Unknown task type "%s"', $step['type']));
            }
        }
    }

    public function configure(?array $config): void
    {
        $this->config = $config;
    }

    protected function executeSource(array $config): array
    {
        if (!array_key_exists($config['which'], $this->sources)) {
            throw new \InvalidArgumentException(sprintf('Unknown source type "%s"', $config['which']));
        }

        $this->logger->info(sprintf('Executing source "%s"', $config['which']));

        $source = $this->sources[$config['which']];
        $source->configure($config['config'] ?? []);

        return $source->execute();
    }

    protected function executeFilter(array $config, array $data)
    {
        if (!array_key_exists($config['which'], $this->conditions)) {
            throw new \InvalidArgumentException(sprintf('Unknown filter type "%s"', $config['which']));
        }

        $this->logger->info(sprintf('Applying filter "%s"', $config['which']));

        $condition = $this->conditions[$config['which']];
        $condition->configure($config['config'] ?? []);

        return array_filter(
            $data,
            function (array $item) use ($condition) {
                return $condition->satisfy($item);
            }
        );
    }

    protected function executeAction(array $config, array $data): array
    {
        if (!array_key_exists($config['which'], $this->actions)) {
            throw new \InvalidArgumentException(sprintf('Unknown action type "%s"', $config['which']));
        }

        $this->logger->info(sprintf('Executing action "%s"', $config['which']));

        $action = $this->actions[$config['which']];
        $action->configure($config['config'] ?? []);

        $localData = $data;
        if (!empty($config['conditions'])) {
            foreach ($config['conditions'] as $condition) {
                $localData = $this->executeFilter($condition, $data);
            }
        }

        foreach ($localData as $key => $item) {
            $data[$key] = $action->execute($item);
        }

        return $data;
    }

    protected function assertIsConfigured(): void
    {
        if (null === $this->config) {
            throw new \RuntimeException('Task must be configured before beeing executed');
        }
    }
}
