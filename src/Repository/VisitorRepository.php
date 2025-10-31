<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Enum\VisitorStatus;

/**
 * @extends ServiceEntityRepository<Visitor>
 */
#[Autoconfigure(public: true)]
#[AsRepository(entityClass: Visitor::class)]
class VisitorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Visitor::class);
    }

    /**
     * 根据预约时间范围查找访客
     * @return array<int, Visitor>
     */
    public function findByAppointmentDateRange(\DateTime $startDate, \DateTime $endDate): array
    {
        /** @var array<int, Visitor> */
        return $this->createQueryBuilder('v')
            ->where('v.appointmentTime BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('v.appointmentTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找待审批的访客
     * @return array<int, Visitor>
     */
    public function findPendingApprovals(): array
    {
        return $this->findByStatus(VisitorStatus::PENDING);
    }

    /**
     * 根据状态查找访客
     * @return array<int, Visitor>
     */
    public function findByStatus(VisitorStatus $status): array
    {
        /** @var array<int, Visitor> */
        return $this->createQueryBuilder('v')
            ->where('v.status = :status')
            ->setParameter('status', $status)
            ->orderBy('v.createTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 分页查找访客
     * @return array<int, Visitor>
     */
    public function findWithPagination(int $page, int $limit): array
    {
        if ($page < 1) {
            $page = 1;
        }
        if ($limit < 1) {
            $limit = 10;
        }

        $offset = ($page - 1) * $limit;

        /** @var array<int, Visitor> */
        return $this->createQueryBuilder('v')
            ->orderBy('v.createTime', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 按状态统计访客数量
     */
    public function countByStatus(VisitorStatus $status): int
    {
        return (int) $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->where('v.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * 查找访客列表（支持多条件过滤）
     * @return array<int, Visitor>
     */
    public function findByMultipleCriteria(
        ?string $name = null,
        ?string $mobile = null,
        ?string $company = null,
        ?VisitorStatus $status = null,
        ?\DateTime $appointmentFrom = null,
        ?\DateTime $appointmentTo = null,
        int $page = 1,
        int $limit = 20,
    ): array {
        $qb = $this->createQueryBuilder('v');

        if (null !== $name) {
            $qb->andWhere('v.name LIKE :name')
                ->setParameter('name', '%' . $name . '%')
            ;
        }

        if (null !== $mobile) {
            $qb->andWhere('v.mobile = :mobile')
                ->setParameter('mobile', $mobile)
            ;
        }

        if (null !== $company) {
            $qb->andWhere('v.company LIKE :company')
                ->setParameter('company', '%' . $company . '%')
            ;
        }

        if (null !== $status) {
            $qb->andWhere('v.status = :status')
                ->setParameter('status', $status)
            ;
        }

        if (null !== $appointmentFrom) {
            $qb->andWhere('v.appointmentTime >= :appointmentFrom')
                ->setParameter('appointmentFrom', $appointmentFrom)
            ;
        }

        if (null !== $appointmentTo) {
            $qb->andWhere('v.appointmentTime <= :appointmentTo')
                ->setParameter('appointmentTo', $appointmentTo)
            ;
        }

        $offset = ($page - 1) * $limit;

        /** @var array<int, Visitor> */
        return $qb->orderBy('v.createTime', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 按日期范围和状态统计访客数量
     */
    public function countByDateRangeAndStatus(
        \DateTime $startDate,
        \DateTime $endDate,
        ?VisitorStatus $status = null,
    ): int {
        $qb = $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->where('v.createTime BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
        ;

        if (null !== $status) {
            $qb->andWhere('v.status = :status')
                ->setParameter('status', $status)
            ;
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * 按日期查找访客
     * @return array<int, Visitor>
     */
    public function findByDate(\DateTime $date): array
    {
        $startOfDay = (clone $date)->setTime(0, 0, 0);
        $endOfDay = (clone $date)->setTime(23, 59, 59);

        /** @var array<int, Visitor> */
        return $this->createQueryBuilder('v')
            ->where('v.createTime BETWEEN :startOfDay AND :endOfDay')
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay)
            ->orderBy('v.createTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 按日期范围查找访客
     * @return array<int, Visitor>
     */
    public function findByDateRange(\DateTime $startDate, \DateTime $endDate): array
    {
        /** @var array<int, Visitor> */
        return $this->createQueryBuilder('v')
            ->where('v.createTime BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('v.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 获取来访最多的公司排名
     *
     * @return array<array{company: string, count: int}>
     */
    public function getTopCompaniesByVisitorCount(
        \DateTime $startDate,
        \DateTime $endDate,
        int $limit = 10,
    ): array {
        /** @var array<array{company: string, count: string}> */
        $result = $this->createQueryBuilder('v')
            ->select('v.company, COUNT(v.id) as count')
            ->where('v.createTime BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->groupBy('v.company')
            ->orderBy('count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;

        return array_map(static function (array $row): array {
            return [
                'company' => $row['company'],
                'count' => (int) $row['count'],
            ];
        }, $result);
    }

    /**
     * 获取日期范围内每日访客数量统计
     *
     * @return array<array{date: string, count: int}>
     */
    public function getVisitorCountByDateRange(
        \DateTime $startDate,
        \DateTime $endDate,
    ): array {
        /** @var array<array{date: string, count: string}> */
        $result = $this->createQueryBuilder('v')
            ->select('DATE(v.createTime) as date, COUNT(v.id) as count')
            ->where('v.createTime BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        return array_map(static function (array $row): array {
            return [
                'date' => $row['date'],
                'count' => (int) $row['count'],
            ];
        }, $result);
    }

    public function save(Visitor $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Visitor $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
