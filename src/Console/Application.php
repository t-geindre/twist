<?php

namespace App\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Application extends BaseApplication
{
    /** @var ContainerInterface */
    private $container;

    /** @var ArgvInput */
    private $input;

    /** @var ConsoleOutput */
    private $output;

    public function __construct(ContainerInterface $container, string $name = 'UNKNOWN', string $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);

        $this->container = $container;

        $this->input = new ArgvInput();
        $this->output = new ConsoleOutput();

        $this->container->set(InputInterface::class, $this->input);
        $this->container->set(OutputInterface::class, $this->output);
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        return parent::run($this->input, $this->output);
    }
}
