<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Entity\VisitorApproval;
use Tourze\VisitorManageBundle\Enum\ApprovalStatus;

/**
 * @internal
 */
#[CoversClass(VisitorApproval::class)]
class VisitorApprovalTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new VisitorApproval();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'status' => ['status', ApprovalStatus::PENDING],
        ];
    }

    public function testApprovalCreation(): void
    {
        $approval = new VisitorApproval();

        $this->assertInstanceOf(VisitorApproval::class, $approval);
        $this->assertNull($approval->getId());
        $this->assertEquals(ApprovalStatus::PENDING, $approval->getStatus());
        $this->assertNull($approval->getRejectReason());
    }

    public function testCustomGettersAndSetters(): void
    {
        $approval = new VisitorApproval();
        $visitor = new Visitor();
        $approveTime = new \DateTimeImmutable();
        $createTime = new \DateTimeImmutable();

        // Test visitor relation
        $approval->setVisitor($visitor);
        $this->assertSame($visitor, $approval->getVisitor());

        // Test status
        $approval->setStatus(ApprovalStatus::APPROVED);
        $this->assertEquals(ApprovalStatus::APPROVED, $approval->getStatus());

        // Test reject reason
        $approval->setRejectReason('安全原因');
        $this->assertEquals('安全原因', $approval->getRejectReason());

        // Test approved at
        $approval->setApproveTime($approveTime);
        $this->assertEquals($approveTime, $approval->getApproveTime());

        // Test created at
        $approval->setCreateTime($createTime);
        $this->assertEquals($createTime, $approval->getCreateTime());
    }

    public function testStatusValidation(): void
    {
        $approval = new VisitorApproval();

        // Test all possible status values
        $approval->setStatus(ApprovalStatus::PENDING);
        $this->assertEquals(ApprovalStatus::PENDING, $approval->getStatus());

        $approval->setStatus(ApprovalStatus::APPROVED);
        $this->assertEquals(ApprovalStatus::APPROVED, $approval->getStatus());

        $approval->setStatus(ApprovalStatus::REJECTED);
        $this->assertEquals(ApprovalStatus::REJECTED, $approval->getStatus());
    }

    public function testRejectReasonOptional(): void
    {
        $approval = new VisitorApproval();

        // Reject reason should be optional
        $this->assertNull($approval->getRejectReason());

        // Should be able to set and unset
        $approval->setRejectReason('违反安全规定');
        $this->assertEquals('违反安全规定', $approval->getRejectReason());

        $approval->setRejectReason(null);
        $this->assertNull($approval->getRejectReason());
    }

    public function testLongRejectReason(): void
    {
        $approval = new VisitorApproval();
        $longReason = str_repeat('这是一个很长的拒绝原因。', 50);

        $approval->setRejectReason($longReason);
        $this->assertEquals($longReason, $approval->getRejectReason());
        $this->assertGreaterThan(100, strlen($approval->getRejectReason() ?? ''));
    }
}
