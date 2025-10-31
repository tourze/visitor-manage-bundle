<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\VisitorManageBundle\Entity\VisitorInvitation;
use Tourze\VisitorManageBundle\Enum\InvitationStatus;

/**
 * @extends ServiceEntityRepository<VisitorInvitation>
 */
#[Autoconfigure(public: true)]
#[AsRepository(entityClass: VisitorInvitation::class)]
class VisitorInvitationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VisitorInvitation::class);
    }

    public function findByInviteCode(string $inviteCode): ?VisitorInvitation
    {
        return $this->findOneBy(['inviteCode' => $inviteCode]);
    }

    /**
     * @return array<int, VisitorInvitation>
     */
    public function findExpiredInvitations(): array
    {
        /** @var array<int, VisitorInvitation> */
        return $this->createQueryBuilder('vi')
            ->where('vi.expireTime < :now')
            ->andWhere('vi.status != :expiredStatus')
            ->setParameter('now', new \DateTime())
            ->setParameter('expiredStatus', InvitationStatus::EXPIRED)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return array<int, VisitorInvitation>
     */
    public function findByInviter(int $inviterId): array
    {
        /** @var array<int, VisitorInvitation> */
        return $this->createQueryBuilder('vi')
            ->where('vi.inviter = :inviterId')
            ->setParameter('inviterId', $inviterId)
            ->orderBy('vi.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(VisitorInvitation $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VisitorInvitation $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
