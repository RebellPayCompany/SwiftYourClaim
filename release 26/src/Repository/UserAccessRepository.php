<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserAccessRepository extends EntityRepository
{
    public function findAccessByUser(User $user)
    {
        $qb = $this->createQueryBuilder('ua');
        $qb
            ->select('ua')
            ->where('ua.manager = :user OR ua.issuer = :user')
            ->setParameter('user', $user);

        return $qb->getQuery()->getOneOrNullResult();
    }
}