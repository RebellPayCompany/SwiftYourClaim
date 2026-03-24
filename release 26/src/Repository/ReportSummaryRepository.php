<?php

namespace App\Repository;

use App\Entity\Company;
use Doctrine\ORM\EntityRepository;

class ReportSummaryRepository extends EntityRepository
{
    public function countReport(Company $company)
    {
        $qb = $this->createQueryBuilder('rs');
        $qb
            ->select('COUNT(rs)')
            ->where('rs.company = :company')
            ->andWhere('rs.date >= :dateStart and rs.date <= :dateEnd')
            ->setParameter('company', $company)
            ->setParameter('dateStart', date("Y") . "-01-01")
            ->setParameter('dateEnd', date("Y") . "-12-31");

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findAllByCompany(Company $company)
    {
        $qb = $this->createQueryBuilder('rs');
        $qb
            ->select('rs')
            ->where('rs.company = :company')
            ->setParameter('company', $company)
            ->orderBy('rs.id', 'DESC');

        return $qb->getQuery()->getResult();
    }
}