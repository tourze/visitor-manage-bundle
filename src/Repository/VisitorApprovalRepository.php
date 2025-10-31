<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\VisitorManageBundle\Entity\VisitorApproval;

/**
 * @extends ServiceEntityRepository<VisitorApproval>
 */
#[Autoconfigure(public: true)]
#[AsRepository(entityClass: VisitorApproval::class)]
class VisitorApprovalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VisitorApproval::class);
    }

    public function save(VisitorApproval $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VisitorApproval $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return array<int, VisitorApproval>
     */
    public function findByApprover(int $approverId): array
    {
        /** @var array<int, VisitorApproval> */
        return $this->createQueryBuilder('va')
            ->where('va.approver = :approverId')
            ->setParameter('approverId', $approverId)
            ->orderBy('va.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return array<int, VisitorApproval>
     */
    public function findByStatus(string $status): array
    {
        /** @var array<int, VisitorApproval> */
        return $this->createQueryBuilder('va')
            ->where('va.status = :status')
            ->setParameter('status', $status)
            ->orderBy('va.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return array<int, VisitorApproval>
     */
    public function findByDateRange(\DateTime $startDate, \DateTime $endDate): array
    {
        /** @var array<int, VisitorApproval> */
        return $this->createQueryBuilder('va')
            ->where('va.createTime BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('va.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
