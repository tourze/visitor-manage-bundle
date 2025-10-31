<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Entity\VisitorPass;

/**
 * @internal
 */
#[CoversClass(VisitorPass::class)]
class VisitorPassTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new VisitorPass();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'passCode' => ['passCode', 'test_value'],
            'qrCode' => ['qrCode', 'test_value'],
        ];
    }

    public function testPassCreation(): void
    {
        $pass = new VisitorPass();

        $this->assertInstanceOf(VisitorPass::class, $pass);
        $this->assertNull($pass->getId());
        $this->assertNull($pass->getUseTime());
    }

    public function testCustomGettersAndSetters(): void
    {
        $pass = new VisitorPass();
        $visitor = new Visitor();
        $validStartTime = new \DateTimeImmutable();
        $validEndTime = new \DateTimeImmutable('+1 day');
        $useTime = new \DateTimeImmutable();
        $createTime = new \DateTimeImmutable();

        // Test visitor relation
        $pass->setVisitor($visitor);
        $this->assertSame($visitor, $pass->getVisitor());

        // Test pass code
        $pass->setPassCode('VP123456789');
        $this->assertEquals('VP123456789', $pass->getPassCode());

        // Test QR code
        $pass->setQrCode('QR_CODE_CONTENT_BASE64');
        $this->assertEquals('QR_CODE_CONTENT_BASE64', $pass->getQrCode());

        // Test valid from
        $pass->setValidStartTime($validStartTime);
        $this->assertEquals($validStartTime, $pass->getValidStartTime());

        // Test valid to
        $pass->setValidEndTime($validEndTime);
        $this->assertEquals($validEndTime, $pass->getValidEndTime());

        // Test used at
        $pass->setUseTime($useTime);
        $this->assertEquals($useTime, $pass->getUseTime());

        // Test created at
        $pass->setCreateTime($createTime);
        $this->assertEquals($createTime, $pass->getCreateTime());
    }

    public function testPassCodeUniqueness(): void
    {
        $pass = new VisitorPass();

        // Test that pass code can be set
        $pass->setPassCode('VP-2024-001-ABCDEF');
        $this->assertEquals('VP-2024-001-ABCDEF', $pass->getPassCode());

        // Uniqueness validation will be handled by service layer
    }

    public function testValidityPeriod(): void
    {
        $pass = new VisitorPass();
        $now = new \DateTimeImmutable();
        $tomorrow = new \DateTimeImmutable('+1 day');

        $pass->setValidStartTime($now);
        $pass->setValidEndTime($tomorrow);

        $this->assertEquals($now, $pass->getValidStartTime());
        $this->assertEquals($tomorrow, $pass->getValidEndTime());
        $this->assertTrue($tomorrow > $now);

        // Validity check logic will be in service layer
    }

    public function testUsageTracking(): void
    {
        $pass = new VisitorPass();

        // Initially not used
        $this->assertNull($pass->getUseTime());

        // Mark as used
        $usedTime = new \DateTimeImmutable();
        $pass->setUseTime($usedTime);
        $this->assertEquals($usedTime, $pass->getUseTime());

        // Can be reset to null if needed
        $pass->setUseTime(null);
        $this->assertNull($pass->getUseTime());
    }

    public function testQrCodeContent(): void
    {
        $pass = new VisitorPass();
        $longQrContent = str_repeat('QR_CODE_DATA_', 100);

        $pass->setQrCode($longQrContent);
        $this->assertEquals($longQrContent, $pass->getQrCode());
        $this->assertGreaterThan(1000, strlen($pass->getQrCode()));
    }

    public function testVisitorRelation(): void
    {
        $pass = new VisitorPass();
        $visitor = new Visitor();

        $pass->setVisitor($visitor);
        $this->assertSame($visitor, $pass->getVisitor());

        // Test one-to-one relationship expectation
        // (actual enforcement will be in database constraints)
    }
}
