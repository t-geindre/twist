<?php

namespace Twist\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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

    /** @var bool */
    private $loginRequired;

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
        $this->setupTasks($input->getOption('task'));

        if ($this->loginRequired) {
            [$username, $password] = $this->getCredentials($this->config, $this->io, $input);
            $this->client->login($username, $password);
        }

        $this->scheduler->run($input->getOption('once'));
    }

    protected function configure()
    {
        $this->setDescription('Run configured tasks');

        $this->addOption(
            'once',
            'o',
            InputOption::VALUE_NONE,
            'Run tasks once, pauses are ignored if once option is enabled'
        );

        $this->addOption(
            'task',
            't',
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'Select specific task to run, will run task even if "run" option is set to false'
        );
    }

    protected function setupTasks(array $tasks = []): void
    {
        $loginRequired = false;

        foreach ($this->config->get('tasks', []) as $taskName => $taskConfig) {
            if (!empty($tasks) && !in_array($taskName, $tasks)) {
                continue;
            }

            if (empty($tasks) && !(bool) ($taskConfig['run'] ?? true)) {
                continue;
            }

            $this->scheduler->addTask(
                $task = $this->taskFactory->create(array_merge(
                    ['name' => $taskName],
                    $taskConfig
                ))
            );

            $loginRequired = $task->isLoginRequired() ? true : $loginRequired;
        }

        if (!empty($tasks) && $this->scheduler->getTaskCount() !== count($tasks)) {
            throw new InvalidArgumentException("Some task couldn't be found");
        }

        $this->loginRequired = $loginRequired;
    }
}
