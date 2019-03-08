<?php

namespace Twist\Console\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConsoleLogger extends AbstractLogger
{
    const VERBOSITY_LEVEL_MAP = [
        LogLevel::EMERGENCY => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::ALERT => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::CRITICAL => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::ERROR => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::WARNING => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::NOTICE => OutputInterface::VERBOSITY_VERBOSE,
        LogLevel::INFO => OutputInterface::VERBOSITY_VERY_VERBOSE,
        LogLevel::DEBUG => OutputInterface::VERBOSITY_DEBUG,
    ];

    const FORMAT_LEVEL_MAP = [
        LogLevel::EMERGENCY => 'error',
        LogLevel::ALERT => 'error',
        LogLevel::CRITICAL => 'error',
        LogLevel::ERROR => 'error',
        LogLevel::WARNING => 'warning',
        LogLevel::NOTICE => 'block',
        LogLevel::INFO => 'block',
        LogLevel::DEBUG => 'block',
    ];

    /** @var OutputInterface */
    private $output;

    /** @var SymfonyStyle */
    private $io;

    public function __construct(OutputInterface $output, SymfonyStyle $io)
    {
        $this->output = $output;
        $this->io = $io;
    }

    public function log($level, $message, array $context = [])
    {
        if ($this->output->getVerbosity() >= self::VERBOSITY_LEVEL_MAP[$level]) {
            $callable = [$this->io, self::FORMAT_LEVEL_MAP[$level]];
            if (is_callable($callable)) {
                call_user_func($callable, $message);
            }
        }
    }
}
