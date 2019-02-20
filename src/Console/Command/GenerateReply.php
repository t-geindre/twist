<?php

namespace Twist\Console\Command;

use Twist\Configuration\Configuration;
use Twist\Twitter\Task\Step\Action\ActionInterface;
use Twist\Twitter\Task\TaskFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateReply extends Command
{
    protected static $defaultName = 'generate-reply';

    /** @var Configuration */
    private $configuration;

    /** @var TaskFactory */
    private $taskFactory;

    public function __construct(Configuration $configuration, TaskFactory $taskFactory)
    {
        $this->configuration = $configuration;

        parent::__construct();
        $this->taskFactory = $taskFactory;
    }

    protected function configure()
    {
        $this->addArgument('task', InputArgument::REQUIRED, 'Task containing reply config');
        $this->addOption('status', null, InputOption::VALUE_OPTIONAL, 'Status content to reply to', 'Hi there! #hello #welcome');
        $this->addOption('author', null, InputOption::VALUE_OPTIONAL, 'Status author to reply to', '@johndoe');
        $this->addOption('count', null, InputOption::VALUE_OPTIONAL, 'Number of replies to generate', 1);
        $this->setDescription('Generate reply according to given task configuration');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tweet = [
            'user' => ['screen_name' => str_replace('@', '', $input->getOption('author'))],
            'text' => $input->getOption('status'),
            'full_text' => $input->getOption('status'),
            'entities' => []
        ];

        preg_match_all('/#([^ ]+)/i', $input->getOption('status'), $matches);

        $hashtags = array_map(function (string $tag) {
            return ['text' => $tag];
        }, $matches[1] ?? []);

        if (count($hashtags)) {
            $tweet['entities']['hashtags'] = $hashtags;
        }

        $tasks = $this->configuration->get('tasks', []);
        $taskName = $input->getArgument('task');

        if (!array_key_exists($taskName, $tasks)) {
            throw new \InvalidArgumentException(sprintf('"%s" task not found', $taskName));
        }

        $steps = array_filter(
            $tasks[$taskName]['steps'] ?? [],
            function (array $step) {
                $type = 'Action\Tweet\Reply';
                return strpos($step['type'], $type) === 0 && $step['type'] !== $type;
            }
        );

        /** @var ActionInterface[] $steps */
        $steps = $this->taskFactory->getSteps($steps);

        $count = (int) $input->getOption('count');

        do {
            foreach ($steps as $step) {
                $tweet = $step->execute($tweet);
            }

            $output->writeln($tweet[ActionInterface::EXTRA_FIELDS_NAMESPACE]['reply']);

            unset($tweet[ActionInterface::EXTRA_FIELDS_NAMESPACE]);
        } while (--$count > 0);
    }
}
