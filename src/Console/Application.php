<?php

namespace Twist\Console;

use Psr\Container\ContainerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Application extends BaseApplication
{
    const COMMAND_TAG = 'twist.command';

    /** @var ContainerBuilder */
    private $container;

    public function __construct(ContainerBuilder $container = null)
    {
        parent::__construct('Twist', '1.0');

        $this->container = null === $container ? new ContainerBuilder() : $container;

        $this
            ->getDefinition()
            ->addOptions([
                new InputOption('configuration-file', 'c', InputOption::VALUE_OPTIONAL, 'Configuration file path'),
                new InputOption('no-headless', null, InputOption::VALUE_NONE, 'Disable headless mode (browser becomes visible)'),
            ]);
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $rootDir = realpath(__DIR__.'/../../').'/';

        $this->container->setParameter('root_dir', $rootDir);

        $loader = new YamlFileLoader($this->container, new FileLocator($rootDir.'config'));
        $loader->load('services.yaml');

        $this->container->setParameter(
            'browser.headless',
            !$input->hasParameterOption(['--no-headless'], true)
        );

        if ($input->hasParameterOption(['--configuration-file', '-c'])) {
            $this->container->setParameter(
                'configuration.file',
                $input->getParameterOption(['--configuration-file', '-c'])
            );
        }

        $this->container->compile(true);

        $this->registerSyntheticServices($input, $output);
        $this->registerCommands();

        return parent::doRun(
            $this->container->get(InputInterface::class),
            $this->container->get(OutputInterface::class)
        );
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

    protected function registerSyntheticServices(InputInterface $input, OutputInterface $output)
    {
        $this->container->set(InputInterface::class, $input);
        $this->container->set(OutputInterface::class, $output);
        $this->container->set(ContainerInterface::class, $this->container);
    }
}
