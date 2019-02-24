<?php

namespace Twist\Twitter\Task\Step\Action\User;

use Doctrine\ORM\EntityManager;
use Twist\Twitter\Api\Client;
use Twist\Twitter\Repository\FriendshipRepository;
use Twist\Twitter\Task\Step\Action\ActionInterface;

class Unfriendship implements ActionInterface
{
    /** @var Client */
    private $client;

    /** @var FriendshipRepository */
    private $friendshipRepository;

    /** @var EntityManager */
    private $em;

    public function __construct(
        Client $client,
        FriendshipRepository $friendshipRepository,
        EntityManager $em
    ) {
        $this->client = $client;
        $this->friendshipRepository = $friendshipRepository;
        $this->em = $em;
    }

    public function execute(array $user): ?array
    {
        $this->client->destroyFriendship(['user_id' => $user['id_str']]);

        $friendship = $this->friendshipRepository->find($user['id_str']);

        if (null !== $friendship) {
            $this->em->remove($friendship);
            $this->em->flush();
        }

        return [];
    }
}
