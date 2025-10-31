<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\VisitorManageBundle\DTO\VisitorRegistrationData;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Enum\VisitorStatus;
use Tourze\VisitorManageBundle\Exception\InvalidVisitorDataException;
use Tourze\VisitorManageBundle\Service\VisitorRegistrationService;

/**
 * @internal
 */
#[CoversClass(VisitorRegistrationService::class)]
#[RunTestsInSeparateProcesses]
class VisitorRegistrationServiceTest extends AbstractIntegrationTestCase
{
    private VisitorRegistrationService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(VisitorRegistrationService::class);
    }

    public function testRegisterVisitorSuccess(): void
    {
        $data = $this->createRegistrationData();
        $operatorId = 123;

        $visitor = $this->service->registerVisitor($data, $operatorId);

        $this->assertInstanceOf(Visitor::class, $visitor);
        $this->assertEquals($data->name, $visitor->getName());
        $this->assertEquals($data->mobile, $visitor->getMobile());
        $this->assertEquals($data->company, $visitor->getCompany());
        $this->assertEquals($data->reason, $visitor->getReason());
        $this->assertEquals($data->appointmentTime, $visitor->getAppointmentTime());
        $this->assertEquals($data->vehicleNumber, $visitor->getVehicleNumber());
        $this->assertEquals(VisitorStatus::PENDING, $visitor->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $visitor->getCreateTime());
        $this->assertInstanceOf(\DateTimeImmutable::class, $visitor->getUpdateTime());
    }

    public function testRegisterVisitorWithMinimalData(): void
    {
        $data = new VisitorRegistrationData();
        $data->name = '张三';
        $data->mobile = '13800138000';
        $data->company = '测试公司';
        $data->reason = '商务洽谈';
        $data->appointmentTime = new \DateTime('+1 day');

        $operatorId = 456;

        $visitor = $this->service->registerVisitor($data, $operatorId);

        $this->assertInstanceOf(Visitor::class, $visitor);
        $this->assertNull($visitor->getVehicleNumber());
        $this->assertNull($visitor->getContactPerson());
        $this->assertNull($visitor->getIdCard());
    }

    public function testRegisterVisitorFailsValidation(): void
    {
        $data = $this->createRegistrationData();
        $data->mobile = 'invalid-mobile'; // 无效手机号
        $operatorId = 789;

        $this->expectException(InvalidVisitorDataException::class);

        $this->service->registerVisitor($data, $operatorId);
    }

    public function testUpdateVisitorSuccess(): void
    {
        // 先注册一个访客
        $originalData = $this->createRegistrationData();
        $visitor = $this->service->registerVisitor($originalData, 123);

        // 更新数据
        $updateData = $this->createRegistrationData();
        $updateData->name = '李四';
        $updateData->mobile = '13900139000';
        $operatorId = 111;

        $updatedVisitor = $this->service->updateVisitor($visitor, $updateData, $operatorId);

        $this->assertSame($visitor, $updatedVisitor);
        $this->assertEquals($updateData->name, $updatedVisitor->getName());
        $this->assertEquals($updateData->mobile, $updatedVisitor->getMobile());
        $this->assertInstanceOf(\DateTimeImmutable::class, $updatedVisitor->getUpdateTime());
    }

    public function testCancelVisitorSuccess(): void
    {
        $data = $this->createRegistrationData();
        $visitor = $this->service->registerVisitor($data, 123);
        $operatorId = 333;
        $reason = '临时取消会议';

        $this->service->cancelVisitor($visitor, $operatorId, $reason);

        $this->assertEquals(VisitorStatus::CANCELLED, $visitor->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $visitor->getUpdateTime());
    }

    public function testCancelVisitorWithoutReason(): void
    {
        $data = $this->createRegistrationData();
        $visitor = $this->service->registerVisitor($data, 123);
        $operatorId = 444;

        $this->service->cancelVisitor($visitor, $operatorId);

        $this->assertEquals(VisitorStatus::CANCELLED, $visitor->getStatus());
    }

    public function testBulkRegisterVisitorsSuccess(): void
    {
        $dataList = [
            $this->createRegistrationData('张三', '13800138000'),
            $this->createRegistrationData('李四', '13900139000'),
            $this->createRegistrationData('王五', '15000150000'),
        ];
        $operatorId = 555;

        $visitors = $this->service->bulkRegisterVisitors($dataList, $operatorId);

        $this->assertCount(3, $visitors);
        foreach ($visitors as $i => $visitor) {
            $this->assertInstanceOf(Visitor::class, $visitor);
            $this->assertEquals($dataList[$i]->name, $visitor->getName());
            $this->assertEquals($dataList[$i]->mobile, $visitor->getMobile());
            $this->assertEquals(VisitorStatus::PENDING, $visitor->getStatus());
        }
    }

    public function testBulkRegisterVisitorsEmpty(): void
    {
        $dataList = [];
        $operatorId = 666;

        $visitors = $this->service->bulkRegisterVisitors($dataList, $operatorId);

        $this->assertCount(0, $visitors);
    }

    private function createRegistrationData(
        string $name = '测试访客',
        string $mobile = '13800138000',
    ): VisitorRegistrationData {
        $data = new VisitorRegistrationData();
        $data->name = $name;
        $data->mobile = $mobile;
        $data->company = '测试公司';
        $data->reason = '商务洽谈';
        $data->appointmentTime = new \DateTime('+1 day');
        $data->vehicleNumber = '京A12345';
        $data->contactPerson = '联系人';
        $data->idCard = '110101199001011234';

        return $data;
    }
}
