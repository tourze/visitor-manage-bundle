<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Enum\VisitorStatus;
use Tourze\VisitorManageBundle\Service\VisitorApprovalService;

/**
 * @internal
 */
#[CoversClass(VisitorApprovalService::class)]
#[RunTestsInSeparateProcesses]
class VisitorApprovalServiceTest extends AbstractIntegrationTestCase
{
    private VisitorApprovalService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(VisitorApprovalService::class);
    }

    public function testSubmitForApprovalSuccess(): void
    {
        $visitor = $this->createVisitor();
        $visitor->setStatus(VisitorStatus::PENDING);
        $operatorId = 123;

        $this->service->submitForApproval($visitor, $operatorId);

        $this->assertEquals(VisitorStatus::PENDING, $visitor->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $visitor->getUpdateTime());
    }

    public function testApproveVisitorSuccess(): void
    {
        $visitor = $this->createVisitor();
        $visitor->setStatus(VisitorStatus::PENDING);
        $approver = $this->createBizUser(789);
        $remark = '审批通过，资料完整';

        $this->service->approveVisitor($visitor, $approver, $remark);

        $this->assertEquals(VisitorStatus::APPROVED, $visitor->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $visitor->getUpdateTime());
    }

    public function testRejectVisitorSuccess(): void
    {
        $visitor = $this->createVisitor();
        $visitor->setStatus(VisitorStatus::PENDING);
        $approver = $this->createBizUser(333);
        $remark = '资料不齐全，拒绝通过';

        $this->service->rejectVisitor($visitor, $approver, $remark);

        $this->assertEquals(VisitorStatus::REJECTED, $visitor->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $visitor->getUpdateTime());
    }

    public function testGetPendingApprovals(): void
    {
        $visitors = $this->service->getPendingApprovals();

        // 验证返回的是数组
        $this->assertIsArray($visitors, 'getPendingApprovals should return an array');
    }

    public function testBatchApproveVisitorsSuccess(): void
    {
        $visitors = [
            $this->createVisitor(1),
            $this->createVisitor(2),
        ];
        $approver = $this->createBizUser(555);
        $remark = '批量审批通过';

        foreach ($visitors as $visitor) {
            $visitor->setStatus(VisitorStatus::PENDING);
        }

        $this->service->batchApproveVisitors($visitors, $approver, $remark);

        foreach ($visitors as $visitor) {
            $this->assertEquals(VisitorStatus::APPROVED, $visitor->getStatus());
            $this->assertInstanceOf(\DateTimeImmutable::class, $visitor->getUpdateTime());
        }
    }

    public function testBatchRejectVisitorsSuccess(): void
    {
        $visitors = [
            $this->createVisitor(1),
            $this->createVisitor(2),
        ];
        $approver = $this->createBizUser(777);
        $remark = '批量拒绝，信息不完整';

        foreach ($visitors as $visitor) {
            $visitor->setStatus(VisitorStatus::PENDING);
        }

        $this->service->batchRejectVisitors($visitors, $approver, $remark);

        foreach ($visitors as $visitor) {
            $this->assertEquals(VisitorStatus::REJECTED, $visitor->getStatus());
            $this->assertInstanceOf(\DateTimeImmutable::class, $visitor->getUpdateTime());
        }
    }

    private function createBizUser(int $id, bool $hasApprovalRole = true): object
    {
        return new class($id, $hasApprovalRole) {
            private int $id;

            private bool $hasApprovalRole;

            public function __construct(int $id, bool $hasApprovalRole)
            {
                $this->id = $id;
                $this->hasApprovalRole = $hasApprovalRole;
            }

            public function getId(): int
            {
                return $this->id;
            }

            public function hasRole(string $role): bool
            {
                if ('ROLE_VISITOR_APPROVER' === $role) {
                    return $this->hasApprovalRole;
                }

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
