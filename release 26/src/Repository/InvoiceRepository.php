<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class InvoiceRepository extends EntityRepository
{
    public function findAllByUser(User $user)
    {
        $qb = $this->createQueryBuilder('i');
        $qb
            ->select('i')
            ->where('i.user = :user')
            ->setParameter('user', $user)
            ->orderBy('i.id', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function countByUser(User $user)
    {
        $qb = $this->createQueryBuilder('i');
        $qb
            ->select('COUNT(i)')
            ->where('i.user = :user')
            ->setParameter('user', $user)
            ->andWhere("i.date >= '".date("Y"),"-01-01'")
            ->andWhere("i.date >= '".date("Y"),"-12-31'");

        return $qb->getQuery()->getSingleScalarResult();
    }
}