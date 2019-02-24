<?php

namespace Twist\Twitter\Repository;

use Doctrine\ORM\EntityRepository;

class FriendshipRepository extends EntityRepository
{
    public function getAllCount(): int
    {
        return $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findFirstOut(int $limit): array
    {
        return $this->createQueryBuilder('f')
            ->orderBy('f.updatedAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
