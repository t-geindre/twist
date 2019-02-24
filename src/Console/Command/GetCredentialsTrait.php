<?php

namespace Twist\Console\Command;

use Symfony\Component\Console\Style\SymfonyStyle;
use Twist\Configuration\Configuration;

trait GetCredentialsTrait
{
    protected function getCredentials(Configuration $config, SymfonyStyle $io): array
    {
        do {
            $configUserName = $config->get('username');
            $username = $io->ask('Username', $configUserName);
        } while (empty(trim($username)));

        $config->set('username', $username);

        $password = $io->askHidden('Password (hidden, never stored)');

        return [$username, $password];
    }
}
