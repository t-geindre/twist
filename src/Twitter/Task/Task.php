<?php

namespace App\Twitter\Task;

use App\Scheduler\TaskInterface;
use App\Twitter\Task\Configurable\ConfigurableInterface;

class Task implements TaskInterface, ConfigurableInterface
{
    /** @var array */
    private $config;

    /** @var array */
    private $sources;

    /** @var array */
    private $conditions;

    /** @var array */
    private $actions;

    public function __construct(array $sources, array $conditions, array $actions)
    {
        $this->sources = $sources;
        $this->conditions = $conditions;
        $this->actions = $actions;
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
        // todo
    }

    public function configure(?array $config): void
    {
        $this->config = $config;
    }

    protected function assertIsConfigured(): void
    {
        if (null === $this->config) {
            throw new \RuntimeException('Task must configured before beeing executed');
        }
    }
}
