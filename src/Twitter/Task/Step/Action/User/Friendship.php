<?php

namespace Twist\Twitter\Task\Step\Action\User;

use Doctrine\ORM\EntityManager;
use Twist\Twitter\Entity\Friendship as FriendshipEntity;
use Twist\Twitter\Repository\FriendshipRepository;
use Twist\Twitter\Task\Step\Action\ActionInterface;
use Twist\Twitter\Api\Client;
use Twist\Twitter\Task\ConfigurableInterface;

class Friendship implements ActionInterface, ConfigurableInterface
{
    /** @var Client */
    private $client;

    /** @var array */
    private $config;

    /** @var FriendshipRepository */
    private $friendshipRepository;

    /** @var EntityManager */
    private $em;

    public function __construct(Client $client, FriendshipRepository $friendshipRepository, EntityManager $em)
    {
        $this->client = $client;
        $this->friendshipRepository = $friendshipRepository;
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

        /** @var FriendshipEntity|null $friendship */
        $friendship = $this->friendshipRepository->findOneBy(['id' => $user['id_str']]);

        if (null === $friendship) {
            $friendship = new FriendshipEntity();
        }

        $friendship->setId($user['id_str']);
        $friendship->setExpirationDate(null);
        $friendship->setUserObject($user);

        // Set update date to force entity update
        $friendship->setUpdatedAt(new \DateTime());

        if (!empty($this->config['ttl'])) {
            try {
                $expiration = new \DateTime($this->config['ttl']);
            } catch (\Throwable $e) {
                throw new \InvalidArgumentException('Invalid TTL format');
            }

            $friendship->setExpirationDate($expiration);
        }

        $this->em->persist($friendship);
        $this->em->flush();

        return $user;
    }
}
