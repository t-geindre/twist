<?php

namespace App\Console\Logger;

use Psr\Log\AbstractLogger;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConsoleLogger extends AbstractLogger
{
    public function __construct(SymfonyStyle $io, string $verbosityMinLevel = 'info')
    {
    }

    public function log($level, $message, array $context = [])
    {
    }
}
