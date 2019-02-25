<?php

namespace Twist\Console;

use Psr\Container\ContainerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Twist\Configuration\Configuration;

class Application extends BaseApplication
{
    const COMMAND_TAG = 'twist.command';

    /** @var ContainerInterface */
    private $container;

    /** @var InputInterface */
    private $input;

    /** @var OutputInterface */
    private $output;

    /** @var string */
    private $rootDir;

    public function __construct()
    {
        parent::__construct('Twist', '1.0');

        $this->rootDir = realpath(__DIR__.'/../../').'/';

        $this
            ->getDefinition()
            ->addOptions([
                new InputOption('configuration-file', 'c', InputOption::VALUE_OPTIONAL, 'Configuration file path'),
                new InputOption('no-headless', null, InputOption::VALUE_NONE, 'Disable headless mode (browser becomes visible)'),
            ]);
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->buildContainer();
        $this->registerCommands();

        return parent::doRun($this->input, $this->output);
    }

    protected function buildContainer(): void
    {
        $this->container = new ContainerBuilder();
        $this->container->setParameter('root_dir', $this->rootDir);

        (new YamlFileLoader($this->container, new FileLocator($this->rootDir.'config')))->load('services.yaml');

        $this->container->setParameter(
            'browser.headless',
            !$this->input->hasParameterOption(['--no-headless'], true)
        );

        if ($this->input->hasParameterOption(['--configuration-file', '-c'])) {
            $this->container->setParameter(
                'configuration.file',
                $this->input->getParameterOption(['--configuration-file', '-c'])
            );
        }

        $this->container->compile(true);

        $this->container->set(ContainerInterface::class, $this->container);
        $this->container->set(InputInterface::class, $this->input);
        $this->container->set(OutputInterface::class, $this->output);

    }

    protected function registerCommands()
    {
        $commandServices = $this->container->findTaggedServiceIds(self::COMMAND_TAG);

        foreach ($commandServices as $serviceId => $tags) {
            /** @var $command \Symfony\Component\Console\Command\Command **/
            $command = $this->container->get($serviceId);
            $this->add($command);
        }
    }
}
