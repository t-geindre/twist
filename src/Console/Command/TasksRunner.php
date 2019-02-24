<?php

namespace Twist\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twist\Configuration\Configuration;
use Twist\Scheduler\Scheduler;
use Twist\Twitter\Browser\Client;
use Twist\Twitter\Task\TaskFactory;

class TasksRunner extends Command
{
    use GetCredentialsTrait;

    protected static $defaultName = 'run';

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

        [$username, $password] = $this->getCredentials($this->config, $this->io, $input);

        $this->client->login($username, $password);

        $this->scheduler->run();
    }

    protected function configure()
    {
        $this->setDescription('Run configured tasks');
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


}
