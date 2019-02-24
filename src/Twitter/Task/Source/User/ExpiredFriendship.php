<?php

namespace Twist\Twitter\Task\Source\User;

use Doctrine\ORM\EntityManager;
use Twist\Twitter\Entity\Friendship;
use Twist\Twitter\Repository\FriendshipRepository;
use Twist\Twitter\Task\ConfigurableInterface;
use Twist\Twitter\Task\Source\SourceInterface;

class ExpiredFriendship implements SourceInterface, ConfigurableInterface
{
    /** @var array */
    private $config;

    /** @var EntityManager */
    private $em;

    /** @var FriendshipRepository */
    private $friendshipRepository;

    public function __construct(EntityManager $em, FriendshipRepository $friendshipRepository)
    {
        $this->em = $em;
        $this->friendshipRepository = $friendshipRepository;
    }

    public function configure(array $config): void
    {
        if (empty($config['mode']) || $config['mode'] != 'fifo') {
            throw new \InvalidArgumentException('Provide a valid mode, supported ones: fifo');
        }

        $config['limit'] = (int) ($config['limit'] ?? 0);
        if ($config['limit'] < 1) {
            throw new \InvalidArgumentException('Provide a valid limit, greater or eaqual to 1');
        }

        $this->config = $config;
    }

    public function execute(): array
    {
        $count = $this->friendshipRepository->getAllCount();

        if ($count <= $this->config['limit']) {
            return [];
        }

        $limit = $count - $this->config['limit'];

        if (($this->config['count'] ?? false) && $limit > $this->config['count']) {
            $limit = $this->config['count'];
        }

        return array_map(
            function (Friendship $friendship) {
                return $friendship->getUserObject();
            },
            $this->friendshipRepository->findFirstOut($limit)
        );
    }
}
