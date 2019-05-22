<?php

namespace Twist\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\ProcessSignaledException;
use Symfony\Component\Process\Process;

class KeepAlive extends Command
{
    protected static $defaultName = 'keep-alive';

    /** @var Process */
    private $process;

    /** @var bool  */
    private $shutdownRequested = false;

    /** @var SymfonyStyle */
    private $io;

    public function __construct(SymfonyStyle $io)
    {
        $this->io = $io;
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('cmd', InputArgument::REQUIRED, 'Command to keep alive');
        $this->addOption('kill-delay', 'd', InputOption::VALUE_OPTIONAL, 'Delay (seconds) after which given command will be killed', 0);
        $this->addOption('kill-signal', 's', InputOption::VALUE_OPTIONAL, 'Signal used to kill given command', SIGINT);
        $this->setDescription('Keep the given command alive, if it stops it is restarted');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        declare(ticks=1);
        pcntl_signal(SIGTERM, [$this, 'handleSignal']);
        pcntl_signal(SIGINT, [$this, 'handleSignal']);

        $command = $input->getArgument('cmd');
        $killDelay = (int) $input->getOption('kill-delay');
        $killSignal = (int) $input->getOption('kill-signal');

        while(!$this->shutdownRequested) {
            $this->message('Starting process');
            $this->process = new Process($command);

            $this->process->start(function ($type, $buffer) use ($output) {
                $output->write($buffer);
            });

            if ($killDelay <= 0) {
                $this->waitProcess();
                continue;
            }

            $killDelayLeft = $killDelay * 1000000; // micro sec
            while ($this->process->isRunning()) {
                usleep(100000); // 100 ms
                $killDelayLeft -= 100000;
                if ($killDelayLeft <= 0 && $this->process->isRunning()) {
                    $this->message(sprintf('Kill delay reached, sending kill signal (%d)', $killSignal));
                    $this->sendSignal($killSignal);
                    break;
                }
            }

            $this->waitProcess();
        }
    }

    private function message(string $message)
    {
        $this->io->block($message, 'KEEP-ALIVE', 'fg=black;bg=yellow');
    }

    private function sendSignal(int $signal): void
    {
        if (null !== $this->process && $this->process->isRunning()) {
            try {
                $this->process->signal($signal);
            } catch (LogicException $e) {}
        }
    }

    private function waitProcess(): void
    {
        if (null !== $this->process && $this->process->isRunning()) {
            try {
                $this->process->wait();
            } catch (ProcessSignaledException $e) {
            }
        }

        $this->message('Process stopped');
    }

    public function handleSignal($signal)
    {
        $this->shutdownRequested = true;

        if (null !== $this->process && $this->process->isRunning()) {
            $this->process->signal($signal);
        }
    }
}
