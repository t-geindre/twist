<?php

namespace Twist\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Twist\Configuration\Configuration;
use Twist\Twitter\Task\Step\Action\ActionInterface;
use Twist\Twitter\Task\Step\StepFactory;

class GenerateReply extends Command
{
    protected static $defaultName = 'generate-reply';

    /** @var Configuration */
    private $configuration;

    /** @var StepFactory */
    private $stepFactory;

    public function __construct(Configuration $configuration, StepFactory $stepFactory)
    {
        $this->configuration = $configuration;

        parent::__construct();
        $this->stepFactory = $stepFactory;
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
        /** @var string $author */
        $author = $input->getOption('author');
        /** @var string $status */
        $status = $input->getOption('status');
        /** @var int $count */
        $count = $input->getOption('count');
        /** @var string $taskName */
        $taskName = $input->getArgument('task');

        $tweet = [
            'user' => ['screen_name' => str_replace('@', '', $author)],
            'text' => $status,
            'full_text' => $status,
            'entities' => []
        ];

        preg_match_all('/#([^ ]+)/i', (string) $status, $matches);

        $hashtags = array_map(function (string $tag) {
            return ['text' => $tag];
        }, $matches[1] ?? []);

        if (count($hashtags)) {
            $tweet['entities']['hashtags'] = $hashtags;
        }

        $tasks = $this->configuration->get('tasks', []);

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
        $steps = $this->stepFactory->createMultiple($steps);

        $count = (int) $count;

        do {
            foreach ($steps as $step) {
                /** @var array $tweet */
                $tweet = $step->execute($tweet);
            }

            $output->writeln($tweet[ActionInterface::EXTRA_FIELDS_NAMESPACE]['reply']);

            unset($tweet[ActionInterface::EXTRA_FIELDS_NAMESPACE]);
        } while (--$count > 0);
    }
}
