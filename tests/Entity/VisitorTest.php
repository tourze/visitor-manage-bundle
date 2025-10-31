<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\BizUserBundle\Entity\BizUser;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Enum\VisitorStatus;

/**
 * @internal
 */
#[CoversClass(Visitor::class)]
class VisitorTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Visitor();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'name' => ['name', 'test_value'],
            'mobile' => ['mobile', 'test_value'],
            'company' => ['company', 'test_value'],
            'reason' => ['reason', 'test_value'],
            'status' => ['status', VisitorStatus::PENDING],
        ];
    }

    public function testVisitorCreation(): void
    {
        $visitor = new Visitor();

        $this->assertInstanceOf(Visitor::class, $visitor);
        $this->assertNull($visitor->getId());
        $this->assertEquals(VisitorStatus::PENDING, $visitor->getStatus());
    }

    public function testCustomGettersAndSetters(): void
    {
        $visitor = new Visitor();
        $appointmentTime = new \DateTimeImmutable('+1 day');
        $createTime = new \DateTimeImmutable();
        $updateTime = new \DateTimeImmutable();

        // Test name
        $visitor->setName('张三');
        $this->assertEquals('张三', $visitor->getName());

        // Test mobile
        $visitor->setMobile('13800138000');
        $this->assertEquals('13800138000', $visitor->getMobile());

        // Test company
        $visitor->setCompany('测试公司');
        $this->assertEquals('测试公司', $visitor->getCompany());

        // Test reason
        $visitor->setReason('商务洽谈');
        $this->assertEquals('商务洽谈', $visitor->getReason());

        // Test vehicle number
        $visitor->setVehicleNumber('京A12345');
        $this->assertEquals('京A12345', $visitor->getVehicleNumber());

        // Test appointment time
        $visitor->setAppointmentTime($appointmentTime);
        $this->assertEquals($appointmentTime, $visitor->getAppointmentTime());

        // Test status
        $visitor->setStatus(VisitorStatus::APPROVED);
        $this->assertEquals(VisitorStatus::APPROVED, $visitor->getStatus());

        // Test created at
        $visitor->setCreateTime($createTime);
        $this->assertEquals($createTime, $visitor->getCreateTime());

        // Test updated at
        $visitor->setUpdateTime($updateTime);
        $this->assertEquals($updateTime, $visitor->getUpdateTime());
    }

    public function testDefaultValues(): void
    {
        $visitor = new Visitor();

        $this->assertEquals(VisitorStatus::PENDING, $visitor->getStatus());
        $this->assertNull($visitor->getVehicleNumber());
    }

    public function testVehicleNumberOptional(): void
    {
        $visitor = new Visitor();

        // Vehicle number should be optional
        $this->assertNull($visitor->getVehicleNumber());

        // Should be able to set and unset
        $visitor->setVehicleNumber('京A12345');
        $this->assertEquals('京A12345', $visitor->getVehicleNumber());

        $visitor->setVehicleNumber(null);
        $this->assertNull($visitor->getVehicleNumber());
    }

    public function testStatusEnum(): void
    {
        $visitor = new Visitor();

        // Test all possible status values
        $visitor->setStatus(VisitorStatus::PENDING);
        $this->assertEquals(VisitorStatus::PENDING, $visitor->getStatus());

        $visitor->setStatus(VisitorStatus::APPROVED);
        $this->assertEquals(VisitorStatus::APPROVED, $visitor->getStatus());

        $visitor->setStatus(VisitorStatus::REJECTED);
        $this->assertEquals(VisitorStatus::REJECTED, $visitor->getStatus());

        $visitor->setStatus(VisitorStatus::SIGNED_IN);
        $this->assertEquals(VisitorStatus::SIGNED_IN, $visitor->getStatus());

        $visitor->setStatus(VisitorStatus::SIGNED_OUT);
        $this->assertEquals(VisitorStatus::SIGNED_OUT, $visitor->getStatus());
    }

    public function testRequiredFieldsValidation(): void
    {
        $visitor = new Visitor();

        // These fields should be required (will be validated by Symfony Validator)
        // For now we just test that they can be set
        $visitor->setName('');
        $this->assertEquals('', $visitor->getName());

        $visitor->setMobile('');
        $this->assertEquals('', $visitor->getMobile());

        $visitor->setCompany('');
        $this->assertEquals('', $visitor->getCompany());

        $visitor->setReason('');
        $this->assertEquals('', $visitor->getReason());

        // Actual validation will be tested in VisitorValidationService tests
    }

    public function testDateTimeFields(): void
    {
        $visitor = new Visitor();
        $now = new \DateTimeImmutable();
        $future = new \DateTimeImmutable('+1 hour');

        // Test appointment time
        $visitor->setAppointmentTime($now);
        $this->assertEquals($now, $visitor->getAppointmentTime());

        // Test created at
        $visitor->setCreateTime($now);
        $this->assertEquals($now, $visitor->getCreateTime());

        // Test updated at
        $visitor->setUpdateTime($future);
        $this->assertEquals($future, $visitor->getUpdateTime());

        // Verify they are different instances
        $this->assertNotSame($now, $future);
    }

    public function testLongTextFields(): void
    {
        $visitor = new Visitor();
        $longReason = str_repeat('这是一个很长的拜访原因。', 50);

        $visitor->setReason($longReason);
        $this->assertEquals($longReason, $visitor->getReason());
        $this->assertGreaterThan(100, strlen($visitor->getReason()));
    }
}
