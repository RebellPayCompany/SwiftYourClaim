<?php

namespace App\Repository;

use App\Entity\Report;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class ReportRepository extends EntityRepository
{
    public function findCurrent($user)
    {
        $qb = $this->createQueryBuilder('r');
        $qb
            ->select('r')
            ->setMaxResults(1)
            ->where('r.manager = :user')
            ->orderBy('r.id', 'DESC')
            ->setParameter('user', $user);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findAllByUser($user, $query = false)
    {
        $qb = $this->createQueryBuilder('r');
        $qb
            ->select('r')
            ->where('r.manager = :user')
            ->andWhere('r.company IS NOT NULL')
            ->orderBy('r.id', 'DESC')
            ->setParameter('user', $user);

        if ($query) {
            return $qb->getQuery();
        } else {
            return $qb->getQuery()->getResult();
        }
    }

    public function findNewByCompany($company, $manager = null)
    {
        $qb = $this->createQueryBuilder('r');
        $qb
            ->select('r, d')
            ->leftJoin('r.data', 'd')
            ->andWhere('r.company = :company')
            ->andWhere('r.status = :status')
            ->orderBy('r.id', 'DESC')
            ->setParameter('company', $company)
            ->setParameter('status', Report::STATUS_WAITING);

        if ($manager) {
            $qb->andWhere('r.manager = :manager')
                ->setParameter('manager', $manager);
        }

        return $qb->getQuery()->getResult();
    }

    public function findRestByCompany($company, $manager = null, $data = [], $query = false)
    {
        $qb = $this->createQueryBuilder('r');
        $qb
            ->select('r, d')
            ->leftJoin('r.data', 'd')
            ->andWhere('r.company = :company')
            ->andWhere('r.status = :statusApproved OR r.status = :statusRejected')
            ->orderBy('r.id', 'DESC')
            ->setParameter('company', $company)
            ->setParameter('statusApproved', Report::STATUS_APPROVED)
            ->setParameter('statusRejected', Report::STATUS_REJECTED);

        if ($manager) {
            $qb->andWhere('r.manager = :manager')
                ->setParameter('manager', $manager);
        }

        if ($data && $data['keyword']) {
            $qb->andWhere('d.firstName = :keyword OR d.lastName = :keyword OR CONCAT(d.firstName, \' \', d.lastName) = :keyword OR CONCAT(d.lastName, \' \', d.firstName) = :keyword')
                ->setParameter('keyword', $data['keyword']);
        }

        if ($query) {
            return $qb->getQuery();
        } else {
            return $qb->getQuery()->getResult();
        }
    }

    public function findActualByCompany($company)
    {
        $qb = $this->createQueryBuilder('r');
        $qb
            ->select('r, d, re')
            ->leftJoin('r.data', 'd')
            ->leftJoin('r.relatedEntity', 're')
            ->leftJoin('r.relatedPerson', 'rp')
            ->andWhere('r.company = :company')
            ->andWhere('r.status = :statusApproved')
            ->andWhere('r.active = :active')
            ->orderBy('r.id', 'DESC')
            ->setParameter('company', $company)
            ->setParameter('statusApproved', Report::STATUS_APPROVED)
            ->setParameter('active', true);

        return $qb->getQuery()->getResult();
    }

    public function countReportByManager(User $manager)
    {
        $qb = $this->createQueryBuilder('r');
        $qb
            ->select('COUNT(r)')
            ->where('r.manager = :manager')
            ->andWhere('r.company IS NOT NULL')
            ->setParameter('manager', $manager);

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getActiveByManager($manager)
    {
        $qb = $this->createQueryBuilder('r');
        $qb
            ->select('r')
            ->where('r.manager = :manager')
            ->andWhere('r.active = :active')
            ->setParameter('manager', $manager)
            ->setParameter('active', true);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getNewByManager($manager)
    {
        $qb = $this->createQueryBuilder('r');
        $qb
            ->select('r')
            ->where('r.manager = :manager')
            ->andWhere('r.new = :new')
            ->setParameter('manager', $manager)
            ->setParameter('new', true);

        return $qb->getQuery()->getOneOrNullResult();
    }
}