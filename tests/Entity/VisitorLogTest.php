<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\BizUserBundle\Entity\BizUser;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Entity\VisitorLog;
use Tourze\VisitorManageBundle\Enum\VisitorAction;

/**
 * @internal
 */
#[CoversClass(VisitorLog::class)]
class VisitorLogTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new VisitorLog();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'remark' => ['remark', 'test_value'],
        ];
    }

    public function testLogCreation(): void
    {
        $log = new VisitorLog();

        $this->assertInstanceOf(VisitorLog::class, $log);
        $this->assertNull($log->getId());
        $this->assertNull($log->getOperator());
    }

    public function testCustomGettersAndSetters(): void
    {
        $log = new VisitorLog();
        $visitor = new Visitor();
        $createTime = new \DateTimeImmutable();

        // Test visitor relation
        $log->setVisitor($visitor);
        $this->assertSame($visitor, $log->getVisitor());

        // Test action
        $log->setAction(VisitorAction::REGISTERED);
        $this->assertEquals(VisitorAction::REGISTERED, $log->getAction());

        // Test remark
        $log->setRemark('访客已成功注册');
        $this->assertEquals('访客已成功注册', $log->getRemark());

        // Test created at
        $log->setCreateTime($createTime);
        $this->assertEquals($createTime, $log->getCreateTime());
    }

    public function testActionTypes(): void
    {
        $log = new VisitorLog();

        // Test all possible action values
        $log->setAction(VisitorAction::REGISTERED);
        $this->assertEquals(VisitorAction::REGISTERED, $log->getAction());

        $log->setAction(VisitorAction::APPROVED);
        $this->assertEquals(VisitorAction::APPROVED, $log->getAction());

        $log->setAction(VisitorAction::REJECTED);
        $this->assertEquals(VisitorAction::REJECTED, $log->getAction());

        $log->setAction(VisitorAction::SIGNED_IN);
        $this->assertEquals(VisitorAction::SIGNED_IN, $log->getAction());

        $log->setAction(VisitorAction::SIGNED_OUT);
        $this->assertEquals(VisitorAction::SIGNED_OUT, $log->getAction());
    }

    public function testOperatorRelation(): void
    {
        $log = new VisitorLog();

        // Test setting null operator (system action)
        $log->setOperator(null);
        $this->assertNull($log->getOperator());

        // Note: Actual BizUser testing will be done when integration is available
    }

    public function testRemarkContent(): void
    {
        $log = new VisitorLog();

        // Test short remark
        $log->setRemark('简单备注');
        $this->assertEquals('简单备注', $log->getRemark());

        // Test long remark
        $longRemark = str_repeat('这是一条详细的日志记录。', 20);
        $log->setRemark($longRemark);
        $this->assertEquals($longRemark, $log->getRemark());
        $this->assertGreaterThan(100, strlen($log->getRemark()));

        // Test empty remark
        $log->setRemark('');
        $this->assertEquals('', $log->getRemark());
    }

    public function testVisitorRelation(): void
    {
        $log = new VisitorLog();
        $visitor = new Visitor();

        $log->setVisitor($visitor);
        $this->assertSame($visitor, $log->getVisitor());

        // Test that visitor is required for log context
        // (will be enforced by business logic)
    }

    public function testTimestampAccuracy(): void
    {
        $log = new VisitorLog();
        $timestamp1 = new \DateTimeImmutable();

        usleep(1000); // Sleep for 1ms to ensure different timestamps

        $timestamp2 = new \DateTimeImmutable();

        $log->setCreateTime($timestamp1);
        $this->assertEquals($timestamp1, $log->getCreateTime());

        $log->setCreateTime($timestamp2);
        $this->assertEquals($timestamp2, $log->getCreateTime());

        // Verify timestamps are different
        $this->assertNotEquals($timestamp1, $timestamp2);
    }
}
