<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Enum\VisitorStatus;
use Tourze\VisitorManageBundle\Exception\VisitorOperationException;
use Tourze\VisitorManageBundle\Service\VisitorPassService;

/**
 * @internal
 */
#[CoversClass(VisitorPassService::class)]
#[RunTestsInSeparateProcesses]
final class VisitorPassServiceTest extends AbstractIntegrationTestCase
{
    private VisitorPassService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(VisitorPassService::class);
    }

    public function testGeneratePassSuccess(): void
    {
        $visitor = $this->createApprovedVisitor();
        $operatorId = 123;
        $validHours = 8;

        $pass = $this->service->generatePass($visitor, $operatorId, $validHours);
        self::assertEquals($visitor, $pass->getVisitor());
        self::assertNotEmpty($pass->getPassCode());
        self::assertNotEmpty($pass->getQrCode());
        self::assertInstanceOf(\DateTimeImmutable::class, $pass->getValidStartTime());
        self::assertInstanceOf(\DateTimeImmutable::class, $pass->getValidEndTime());
    }

    public function testGeneratePassVisitorNotApproved(): void
    {
        $visitor = $this->createVisitor();
        $visitor->setStatus(VisitorStatus::PENDING);
        $operatorId = 456;

        $this->expectException(VisitorOperationException::class);
        $this->expectExceptionMessage('访客未通过审批，无法生成通行码');

        $this->service->generatePass($visitor, $operatorId, 8);
    }

    public function testValidatePassValid(): void
    {
        $visitor = $this->createApprovedVisitor();
        $pass = $this->service->generatePass($visitor, 123, 8);

        $result = $this->service->validatePass($pass->getPassCode());

        self::assertTrue($result);
    }

    public function testValidatePassNotFound(): void
    {
        $result = $this->service->validatePass('NOTFOUND123');

        self::assertFalse($result);
    }

    public function testUsePassSuccess(): void
    {
        $visitor = $this->createApprovedVisitor();
        $pass = $this->service->generatePass($visitor, 123, 8);
        $operatorId = 999;

        $this->service->usePass($pass->getPassCode(), $operatorId);

        self::assertInstanceOf(\DateTimeImmutable::class, $pass->getUseTime());
    }

    public function testUsePassAlreadyUsed(): void
    {
        $visitor = $this->createApprovedVisitor();
        $pass = $this->service->generatePass($visitor, 123, 8);

        // 先使用一次
        $this->service->usePass($pass->getPassCode(), 111);

        $this->expectException(VisitorOperationException::class);
        $this->expectExceptionMessage('通行码已被使用');

        // 再次使用应该失败
        $this->service->usePass($pass->getPassCode(), 222);
    }

    public function testIsPassValid(): void
    {
        $visitor = $this->createApprovedVisitor();
        $pass = $this->service->generatePass($visitor, 123, 8);

        $result = $this->service->isPassValid($pass);

        self::assertTrue($result);
    }

    public function testPassCodeUniqueness(): void
    {
        $visitor1 = $this->createApprovedVisitor(1);
        $visitor2 = $this->createApprovedVisitor(2);
        $operatorId = 333;

        $pass1 = $this->service->generatePass($visitor1, $operatorId, 8);
        $pass2 = $this->service->generatePass($visitor2, $operatorId, 8);

        self::assertNotEquals($pass1->getPassCode(), $pass2->getPassCode());
        self::assertTrue(strlen($pass1->getPassCode()) >= 8);
        self::assertTrue(strlen($pass2->getPassCode()) >= 8);
    }

    public function testAutoGeneratePassForApprovedVisitor(): void
    {
        $visitor = $this->createApprovedVisitor();

        // 持久化 visitor 以便测试中能找到
        $em = self::getEntityManager();
        $em->persist($visitor);
        $em->flush();

        $operatorId = 444;

        $visitorId = $visitor->getId();
        self::assertNotNull($visitorId);

        $pass = $this->service->autoGeneratePassForApprovedVisitor($visitorId, $operatorId);
        self::assertEquals($visitorId, $pass->getVisitor()?->getId());
        self::assertNotEmpty($pass->getPassCode());
        self::assertNotEmpty($pass->getQrCode());
    }

    public function testAutoGeneratePassVisitorNotFound(): void
    {
        $nonExistentVisitorId = 99999;
        $operatorId = 555;

        $this->expectException(VisitorOperationException::class);
        $this->expectExceptionMessage('访客 ID 99999 不存在');

        $this->service->autoGeneratePassForApprovedVisitor($nonExistentVisitorId, $operatorId);
    }

    private function createVisitor(int $id = 1): Visitor
    {
        $visitor = new Visitor();
        $visitor->setName('测试访客');
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

    private function createApprovedVisitor(int $id = 1): Visitor
    {
        $visitor = $this->createVisitor($id);
        $visitor->setStatus(VisitorStatus::APPROVED);

        return $visitor;
    }
}
