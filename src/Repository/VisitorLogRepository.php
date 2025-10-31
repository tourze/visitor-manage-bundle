<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Entity\VisitorLog;
use Tourze\VisitorManageBundle\Enum\VisitorAction;

/**
 * @extends ServiceEntityRepository<VisitorLog>
 */
#[Autoconfigure(public: true)]
#[AsRepository(entityClass: VisitorLog::class)]
class VisitorLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VisitorLog::class);
    }

    /**
     * @return VisitorLog[]
     */
    public function findByVisitor(Visitor $visitor): array
    {
        /** @var VisitorLog[] */
        return $this->createQueryBuilder('vl')
            ->where('vl.visitor = :visitor')
            ->setParameter('visitor', $visitor)
            ->orderBy('vl.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return VisitorLog[]
     */
    public function findByDateRange(\DateTime $startDate, \DateTime $endDate): array
    {
        /** @var VisitorLog[] */
        return $this->createQueryBuilder('vl')
            ->where('vl.createTime >= :startDate')
            ->andWhere('vl.createTime <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('vl.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return VisitorLog[]
     */
    public function findByAction(VisitorAction $action): array
    {
        /** @var VisitorLog[] */
        return $this->createQueryBuilder('vl')
            ->where('vl.action = :action')
            ->setParameter('action', $action)
            ->orderBy('vl.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return VisitorLog[]
     */
    public function findByOperator(int $operatorId): array
    {
        /** @var VisitorLog[] */
        return $this->createQueryBuilder('vl')
            ->where('vl.operator = :operatorId')
            ->setParameter('operatorId', $operatorId)
            ->orderBy('vl.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function countByAction(VisitorAction $action): int
    {
        return (int) $this->createQueryBuilder('vl')
            ->select('COUNT(vl.id)')
            ->where('vl.action = :action')
            ->setParameter('action', $action)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function save(VisitorLog $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VisitorLog $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
