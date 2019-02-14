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

        $source = $this->sources[$this->config['source']['type']];
        $source->configure($this->config['source']['config']);

        $data = $source->execute();

        var_dump($data);
    }

    public function configure(?array $config): void
    {
        $this->config = $config;
    }

    protected function assertIsConfigured(): void
    {
        if (null === $this->config) {
            throw new \RuntimeException('Task must be configured before beeing executed');
        }
    }
}
