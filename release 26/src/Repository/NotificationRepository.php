<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class NotificationRepository extends EntityRepository
{
    public function findAllByUser(User $user, $query = false)
    {
        $qb = $this->createQueryBuilder('n');
        $qb
            ->select('n')
            ->where('n.user = :user')
            ->setParameter('user', $user)
            ->orderBy('n.id', 'DESC');

        if ($query) {
            return $qb->getQuery();
        } else {
            return $qb->getQuery()->getResult();
        }
    }

    public function countNewByUser(User $user)
    {
        $qb = $this->createQueryBuilder('n');
        $qb
            ->select('COUNT(n)')
            ->where('n.user = :user')
            ->andWhere('n.readed = :readed')
            ->setParameter('user', $user)
            ->setParameter('readed', false);

        return $qb->getQuery()->getSingleScalarResult();
    }
}