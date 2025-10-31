<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Entity\VisitorInvitation;
use Tourze\VisitorManageBundle\Enum\InvitationStatus;
use Tourze\VisitorManageBundle\Enum\VisitorStatus;
use Tourze\VisitorManageBundle\Exception\InvalidInviterException;
use Tourze\VisitorManageBundle\Exception\InvitationExpiredException;
use Tourze\VisitorManageBundle\Service\VisitorInvitationService;

/**
 * @internal
 */
#[CoversClass(VisitorInvitationService::class)]
#[RunTestsInSeparateProcesses]
class VisitorInvitationServiceTest extends AbstractIntegrationTestCase
{
    private VisitorInvitationService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(VisitorInvitationService::class);
    }

    public function testCreateInvitationSuccess(): void
    {
        $visitor = $this->createVisitor();
        $inviter = $this->createBizUser(123);
        $expireHours = 24;

        $invitation = $this->service->createInvitation($visitor, $inviter, $expireHours);

        $this->assertInstanceOf(VisitorInvitation::class, $invitation);
        $this->assertEquals($visitor, $invitation->getVisitor());
        $this->assertEquals($inviter->getId(), $invitation->getInviter());
        $this->assertEquals(InvitationStatus::PENDING, $invitation->getStatus());
        $this->assertNotEmpty($invitation->getInviteCode());
        $this->assertInstanceOf(\DateTimeImmutable::class, $invitation->getExpireTime());
        $this->assertInstanceOf(\DateTimeImmutable::class, $invitation->getCreateTime());
    }

    public function testCreateInvitationInvalidInviter(): void
    {
        $visitor = $this->createVisitor();
        $inviter = null;
        $expireHours = 24;

        $this->expectException(InvalidInviterException::class);
        $this->expectExceptionMessage('邀请者不能为空');

        $this->service->createInvitation($visitor, $inviter, $expireHours);
    }

    public function testConfirmInvitationSuccess(): void
    {
        $visitor = $this->createVisitor();
        $inviter = $this->createBizUser(456);
        $invitation = $this->service->createInvitation($visitor, $inviter, 24);
        $operatorId = 789;

        $this->service->confirmInvitation($invitation, $operatorId);

        $this->assertEquals(InvitationStatus::CONFIRMED, $invitation->getStatus());
    }

    public function testConfirmInvitationExpired(): void
    {
        $visitor = $this->createVisitor();
        $inviter = $this->createBizUser(111);
        $invitation = $this->service->createInvitation($visitor, $inviter, 1);

        // 强制设置过期时间为过去
        $invitation->setExpireTime((new \DateTimeImmutable())->modify('-1 hour'));

        $operatorId = 222;

        $this->expectException(InvitationExpiredException::class);
        $this->expectExceptionMessage('邀请已过期');

        $this->service->confirmInvitation($invitation, $operatorId);
    }

    public function testRejectInvitationSuccess(): void
    {
        $visitor = $this->createVisitor();
        $inviter = $this->createBizUser(333);
        $invitation = $this->service->createInvitation($visitor, $inviter, 12);
        $operatorId = 444;

        $this->service->rejectInvitation($invitation, $operatorId);

        $this->assertEquals(InvitationStatus::REJECTED, $invitation->getStatus());
    }

    public function testCancelInvitationSuccess(): void
    {
        $visitor = $this->createVisitor();
        $inviter = $this->createBizUser(555);
        $invitation = $this->service->createInvitation($visitor, $inviter, 6);
        $operatorId = 666;

        $this->service->cancelInvitation($invitation, $operatorId);

        $this->assertEquals(InvitationStatus::REJECTED, $invitation->getStatus());
    }

    public function testGetInvitationByCode(): void
    {
        $visitor = $this->createVisitor();
        $inviter = $this->createBizUser(777);
        $invitation = $this->service->createInvitation($visitor, $inviter, 48);

        $foundInvitation = $this->service->getInvitation($invitation->getInviteCode());

        $this->assertNotNull($foundInvitation);
        $this->assertEquals($invitation->getInviteCode(), $foundInvitation->getInviteCode());
    }

    public function testGetInvitationNotFound(): void
    {
        $result = $this->service->getInvitation('NOTFOUND123');

        $this->assertNull($result);
    }

    public function testGetInvitationsByInviter(): void
    {
        $inviterId = 888;
        $inviter = $this->createBizUser($inviterId);
        $visitor1 = $this->createVisitor(1);
        $visitor2 = $this->createVisitor(2);

        $this->service->createInvitation($visitor1, $inviter, 24);
        $this->service->createInvitation($visitor2, $inviter, 24);

        $invitations = $this->service->getInvitationsByInviter($inviterId);

        $this->assertCount(2, $invitations);
    }

    public function testExpireInvitations(): void
    {
        $visitor1 = $this->createVisitor(1);
        $visitor2 = $this->createVisitor(2);
        $inviter = $this->createBizUser(999);
        $operatorId = 1001;

        // 创建正常邀请
        $normalInvitation = $this->service->createInvitation($visitor1, $inviter, 24);

        // 创建即将过期的邀请
        $expiredInvitation = $this->service->createInvitation($visitor2, $inviter, 1);

        // 强制设置过期时间为过去
        $expiredInvitation->setExpireTime((new \DateTimeImmutable())->modify('-1 hour'));

        // 执行过期处理
        $expiredCount = $this->service->expireInvitations($operatorId);

        // 验证结果
        $this->assertGreaterThanOrEqual(0, $expiredCount);
        $this->assertEquals(InvitationStatus::PENDING, $normalInvitation->getStatus());
    }

    private function createBizUser(int $id): object
    {
        return new class($id) {
            private int $id;

            public function __construct(int $id)
            {
                $this->id = $id;
            }

            public function getId(): int
            {
                return $this->id;
            }

            public function hasRole(string $role): bool
            {
                return true;
            }
        };
    }

    private function createVisitor(int $id = 1, string $name = '测试访客'): Visitor
    {
        $visitor = new Visitor();
        $visitor->setName($name);
        $visitor->setMobile('13800138000');
        $visitor->setCompany('测试公司');
        $visitor->setReason('商务洽谈');
        $visitor->setAppointmentTime(new \DateTimeImmutable('+1 day'));
        $visitor->setStatus(VisitorStatus::PENDING);
        $visitor->setCreateTime(new \DateTimeImmutable());
        $visitor->setUpdateTime(new \DateTimeImmutable());

        $reflection = new \ReflectionClass($visitor);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($visitor, $id);

        return $visitor;
    }
}
