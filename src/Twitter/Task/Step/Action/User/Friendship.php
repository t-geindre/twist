<?php

namespace Twist\Twitter\Task\Step\Action\User;

use Doctrine\ORM\EntityManager;
use Twist\Twitter\Entity\FriendshipExpiration;
use Twist\Twitter\Repository\FriendshipExpirationRepository;
use Twist\Twitter\Task\Step\Action\ActionInterface;
use Twist\Twitter\Api\Client;
use Twist\Twitter\Task\ConfigurableInterface;

class Friendship implements ActionInterface, ConfigurableInterface
{
    /** @var Client */
    private $client;

    /** @var bool  */
    private $config = false;

    /** @var FriendshipExpirationRepository */
    private $friendshipExpiration;

    /** @var EntityManager */
    private $em;

    public function __construct(Client $client, FriendshipExpirationRepository $friendshipExpiration, EntityManager $em)
    {
        $this->client = $client;
        $this->friendshipExpiration = $friendshipExpiration;
        $this->em = $em;
    }

    public function configure(array $config): void
    {
        $this->config = $config;
    }

    public function execute(array $user): ?array
    {
        $this->client->createFriendship(array_filter([
            'user_id' => $user['id_str'],
            'follow' => ($this->config['follow'] ?? true) === false ? 'false' : null
        ]));

        if (!empty($this->config['ttl'])) {
            try {
                $expiration = new \DateTime($this->config['ttl']);
            } catch (\Throwable $e) {
                throw new \InvalidArgumentException('Invalid TTL format');
            }

            $friendshipExpiration = $this->friendshipExpiration->findOneBy(['id' => $user['id_str']]);

            if (null === $friendshipExpiration) {
                $friendshipExpiration = new FriendshipExpiration();
            }

            $friendshipExpiration->setId($user['id_str']);
            $friendshipExpiration->setExpirationDate($expiration);
            $friendshipExpiration->setUserObject($user);

            $this->em->persist($friendshipExpiration);
            $this->em->flush();
        }

        return $user;
    }
}
