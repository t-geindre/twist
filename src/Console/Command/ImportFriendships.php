<?php

namespace Twist\Console\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twist\Configuration\Configuration;
use Twist\Twitter\Api\Client;
use Twist\Twitter\Entity\Friendship;
use Twist\Twitter\Repository\FriendshipRepository;

class ImportFriendships extends Command
{
    use GetCredentialsTrait;

    protected static $defaultName = 'import-friendship';

    /** @var Configuration */
    private $config;

    /** @var Client */
    private $client;

    /** @var SymfonyStyle */
    private $io;

    /** @var EntityManager */
    private $em;

    /** @var FriendshipRepository */
    private $friendshipRepository;

    public function __construct(
        Configuration $config,
        Client $client,
        SymfonyStyle $io,
        EntityManager $em, FriendshipRepository $friendshipRepository
    ) {
        $this->config = $config;
        $this->client = $client;
        $this->io = $io;

        parent::__construct();
        $this->em = $em;
        $this->friendshipRepository = $friendshipRepository;
    }

    protected function configure()
    {
        $this->addArgument('start-date', InputArgument::REQUIRED, 'Starting creation date');
        $this->addArgument('date-interval', InputArgument::REQUIRED, 'Time interval between each friendship');
        $this->setDescription('Import existing friendships');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $date = new \DateTime($input->getArgument('start-date'));
        } catch (\Throwable $e) {
            $this->io->error(sprintf('Invalid date format "%s"', $input->getArgument('start-date')));
            exit(1);
        }

        try {
            $dateInterval = new \DateInterval($input->getArgument('date-interval'));
        } catch (\Throwable $e) {
            $this->io->error(sprintf('Invalid time interval format "%s"', $input->getArgument('date-interval')));
            exit(1);
        }

        [$username, $password] = $this->getCredentials($this->config, $this->io);

        $this->client->login($username, $password);

        $count = 0;
        $cursor = -1;

        do {
            $friends = $this->client->getFriendsList(['skip_status' => 1, 'cursor' => $cursor, 'count' => 200]);
            $cursor = $friends['next_cursor'];

            foreach ($friends['users'] as $user) {
                $friendship = $this->friendshipRepository->find($user['id_str']);
                if (null !== $friendship) {
                    $this->io->writeln(sprintf(' <fg=blue>@%s</> already known', $user['screen_name']));
                    continue;
                }

                $friendship = new Friendship();
                $friendship->setId($user['id_str']);
                $friendship->setCreatedAt(clone $date);
                $friendship->setUpdatedAt(clone $date);
                $friendship->setUserObject($user);

                $this->em->persist($friendship);

                $this->io->writeln(sprintf(' <fg=blue>@%s</> imported', $user['screen_name']));

                $date = $date->sub($dateInterval);

                $count++;
            }

            $this->em->flush();
        } while ($cursor != 0);

        $this->io->newLine();
        $this->io->writeln(sprintf('%d accout(s) imported', $count));
    }
}
