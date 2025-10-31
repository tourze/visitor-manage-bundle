<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Entity\VisitorInvitation;
use Tourze\VisitorManageBundle\Enum\InvitationStatus;
use Tourze\VisitorManageBundle\Enum\VisitorStatus;
use Tourze\VisitorManageBundle\Repository\VisitorInvitationRepository;

/**
 * @internal
 */
#[CoversClass(VisitorInvitationRepository::class)]
#[RunTestsInSeparateProcesses]
final class VisitorInvitationRepositoryTest extends AbstractRepositoryTestCase
{
    private VisitorInvitationRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(VisitorInvitationRepository::class);
    }

    protected function getRepository(): VisitorInvitationRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): object
    {
        return $this->createVisitorInvitation([], false);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function createVisitor(array $attributes = [], bool $persist = false): Visitor
    {
        /** @var int $counter */
        static $counter = 0;
        $visitor = new Visitor();

        $uniqueValue = (int) (microtime(true) * 1000000) + (++$counter);

        $attributes = array_merge([
            'name' => '测试访客' . $uniqueValue,
            'mobile' => '1380' . str_pad((string) ($uniqueValue % 10000000), 7, '0', STR_PAD_LEFT),
            'company' => '测试公司' . $uniqueValue,
            'reason' => '测试来访' . $uniqueValue,
            'appointmentTime' => new \DateTimeImmutable('+1 day'),
            'status' => VisitorStatus::PENDING,
        ], $attributes);

        /** @var string $name */
        $name = $attributes['name'];
        /** @var string $mobile */
        $mobile = $attributes['mobile'];
        /** @var string $company */
        $company = $attributes['company'];
        /** @var string $reason */
        $reason = $attributes['reason'];
        /** @var \DateTimeImmutable $appointmentTime */
        $appointmentTime = $attributes['appointmentTime'];
        /** @var VisitorStatus $status */
        $status = $attributes['status'];

        $visitor->setName($name);
        $visitor->setMobile($mobile);
        $visitor->setCompany($company);
        $visitor->setReason($reason);
        $visitor->setAppointmentTime($appointmentTime);
        $visitor->setStatus($status);

        if ($persist) {
            $this->persistAndFlush($visitor);
        }

        return $visitor;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function createVisitorInvitation(array $attributes = [], bool $persist = true): VisitorInvitation
    {
        /** @var int $counter */
        static $counter = 0;
        $uniqueValue = (int) (microtime(true) * 1000000) + (++$counter);

        // 始终创建并先持久化visitor，除非显式传入visitor
        $visitor = $attributes['visitor'] ?? $this->createVisitor([], true);
        $this->assertInstanceOf(Visitor::class, $visitor);

        $attributes = array_merge([
            'visitor' => $visitor,
            'inviteCode' => 'INV' . $uniqueValue,
            'inviter' => 1001,
            'expireTime' => new \DateTimeImmutable('+7 days'),
            'status' => InvitationStatus::PENDING,
            'createTime' => new \DateTimeImmutable(),
        ], $attributes);

        /** @var Visitor $visitorEntity */
        $visitorEntity = $attributes['visitor'];
        /** @var string $inviteCode */
        $inviteCode = $attributes['inviteCode'];
        /** @var int $inviter */
        $inviter = $attributes['inviter'];
        /** @var \DateTimeImmutable $expireTime */
        $expireTime = $attributes['expireTime'];
        /** @var InvitationStatus $status */
        $status = $attributes['status'];
        /** @var \DateTimeImmutable $createTime */
        $createTime = $attributes['createTime'];

        $invitation = new VisitorInvitation();
        $invitation->setInviteCode($inviteCode);
        $invitation->setInviter($inviter);
        $invitation->setVisitor($visitorEntity);
        $invitation->setExpireTime($expireTime);
        $invitation->setStatus($status);
        $invitation->setCreateTime($createTime);

        if ($persist) {
            $this->persistAndFlush($invitation);
        }

        return $invitation;
    }

    public function testFindByInviteCode(): void
    {
        $invitation = $this->createVisitorInvitation(['inviteCode' => 'TEST123']);

        $result = $this->repository->findByInviteCode('TEST123');

        $this->assertInstanceOf(VisitorInvitation::class, $result);
        $this->assertSame('TEST123', $result->getInviteCode());
    }

    public function testFindByInviter(): void
    {
        $this->createVisitorInvitation(['inviter' => 1001]);
        $this->createVisitorInvitation(['inviter' => 1001]);
        $this->createVisitorInvitation(['inviter' => 1002]);

        $result = $this->repository->findByInviter(1001);

        $this->assertGreaterThanOrEqual(2, count($result));
        foreach ($result as $invitation) {
            $this->assertInstanceOf(VisitorInvitation::class, $invitation);
            $this->assertSame(1001, $invitation->getInviter());
        }
    }

    public function testFindExpiredInvitations(): void
    {
        $this->createVisitorInvitation([
            'expireTime' => new \DateTimeImmutable('-1 day'),
            'status' => InvitationStatus::PENDING,
        ]);
        $this->createVisitorInvitation([
            'expireTime' => new \DateTimeImmutable('+1 day'),
            'status' => InvitationStatus::PENDING,
        ]);

        $result = $this->repository->findExpiredInvitations();

        $this->assertGreaterThanOrEqual(1, count($result));
        $this->assertInstanceOf(VisitorInvitation::class, $result[0]);
    }
}
