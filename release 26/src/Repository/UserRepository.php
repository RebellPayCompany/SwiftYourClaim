<?php

namespace App\Repository;

use App\Entity\Company;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function getManagers(Company $company, $query = false)
    {
        $qb = $this->createQueryBuilder('u');
        $qb
            ->select('u, r, m')
            ->leftJoin('u.reportManager', 'r', 'WITH', 'r.new = :new')
            ->leftJoin('u.manager' , 'm')
            ->where('u.companyManager = :company')
            ->andWhere('u.deleted = :deleted')
            ->setParameter('company', $company)
            ->setParameter('deleted', false)
            ->setParameter('new', true)
            ->orderBy('u.id', 'DESC');

        if ($query) {
            return $qb->getQuery();
        } else {
            return $qb->getQuery()->getResult();
        }
    }
}