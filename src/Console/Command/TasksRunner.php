<?php

namespace Twist\Console\Command;

use Twist\Configuration\Configuration;
use Twist\Scheduler\Scheduler;
use Twist\Twitter\Browser\Client;
use Twist\Twitter\Task\TaskFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TasksRunner extends Command
{
    const COMMAND_NAME = 'tasks:run';

    protected static $defaultName = self::COMMAND_NAME;

    /** @var Configuration */
    private $config;

    /** @var Client */
    private $client;

    /** @var TaskFactory */
    private $taskFactory;

    /** @var Scheduler */
    private $scheduler;

    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        Configuration $config,
        Client $client,
        TaskFactory $taskFactory,
        Scheduler $scheduler,
        SymfonyStyle $io
    ) {
        parent::__construct();

        $this->config = $config;
        $this->client = $client;
        $this->taskFactory = $taskFactory;
        $this->scheduler = $scheduler;
        $this->io = $io;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setupTasks();

        [$username, $password] = $this->getCredentials();

        $this->client->login($username, $password);

        $this->scheduler->run();
    }

    protected function setupTasks(): void
    {
        foreach ($this->config->get('tasks', []) as $taskName => $taskConfig) {
            $this->scheduler->addTask(
                $this->taskFactory->create(array_merge(
                    ['name' => $taskName],
                    $taskConfig
                ))
            );
        }
    }

    protected function getCredentials(): array
    {
        do {
            $configUserName = $this->config->get('username');
            $username = $this->io->ask('Username', $configUserName);
        } while (empty(trim($username)));

        $this->config->set('username', $username);

        $password = $this->io->askHidden('Password (hidden, never stored)');

        return [$username, $password];
    }
}
