<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\VisitorManageBundle\Entity\VisitorPass;

/**
 * @extends ServiceEntityRepository<VisitorPass>
 */
#[Autoconfigure(public: true)]
#[AsRepository(entityClass: VisitorPass::class)]
class VisitorPassRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VisitorPass::class);
    }

    public function findByPassCode(string $passCode): ?VisitorPass
    {
        return $this->findOneBy(['passCode' => $passCode]);
    }

    /**
     * @return array<int, VisitorPass>
     */
    public function findValidPasses(): array
    {
        $now = new \DateTime();

        /** @var array<int, VisitorPass> */
        return $this->createQueryBuilder('vp')
            ->where('vp.validStartTime <= :now')
            ->andWhere('vp.validEndTime >= :now')
            ->andWhere('vp.useTime IS NULL')
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return array<int, VisitorPass>
     */
    public function findUsedPasses(): array
    {
        /** @var array<int, VisitorPass> */
        return $this->createQueryBuilder('vp')
            ->where('vp.useTime IS NOT NULL')
            ->orderBy('vp.useTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(VisitorPass $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VisitorPass $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
