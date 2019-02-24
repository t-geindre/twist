<?php

namespace Twist\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twist\Configuration\Configuration;

trait GetCredentialsTrait
{
    protected function getCredentials(Configuration $config, SymfonyStyle $io, InputInterface $input): array
    {
        do {
            $configUserName = $config->get('username');
            $username = $io->ask('Username', $configUserName);
        } while (empty(trim($username)) && $input->isInteractive());

        $config->set('username', $username);

        do {
            $configPassword = $config->get('password');
            $password = $io->askHidden('Password');
            if (null == $password || strlen($password) === 0) {
                $password = $configPassword;
            }
        } while (strlen($password) === 0 && $input->isInteractive());

        if (empty(trim($username)) || strlen($password) === 0) {
            throw new \InvalidArgumentException(
                'Provide configured username and password to run command in no interaction mode'
            );
        }

        return [$username, $password];
    }
}
