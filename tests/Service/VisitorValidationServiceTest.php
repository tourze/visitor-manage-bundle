<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\VisitorManageBundle\DTO\VisitorRegistrationData;
use Tourze\VisitorManageBundle\DTO\VisitorSearchCriteria;
use Tourze\VisitorManageBundle\Exception\InvalidVisitorDataException;
use Tourze\VisitorManageBundle\Service\VisitorService;
use Tourze\VisitorManageBundle\Service\VisitorValidationService;

/**
 * @internal
 */
#[CoversClass(VisitorValidationService::class)]
#[RunTestsInSeparateProcesses]
class VisitorValidationServiceTest extends AbstractIntegrationTestCase
{
    private VisitorValidationService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(VisitorValidationService::class);
    }

    public function testValidateRegistrationDataSuccess(): void
    {
        $data = new VisitorRegistrationData();
        $data->name = '张三';
        $data->mobile = '13800138000';
        $data->company = '测试公司';
        $data->reason = '商务洽谈';
        $data->appointmentTime = new \DateTime('+1 day');

        $result = $this->service->validateRegistrationData($data);

        $this->assertTrue($result);
    }

    public function testValidateRegistrationDataInvalidMobile(): void
    {
        $data = new VisitorRegistrationData();
        $data->name = '张三';
        $data->mobile = 'invalid-mobile';
        $data->company = '测试公司';
        $data->reason = '商务洽谈';
        $data->appointmentTime = new \DateTime('+1 day');

        $this->expectException(InvalidVisitorDataException::class);

        $this->service->validateRegistrationData($data);
    }

    public function testValidateRegistrationDataEmptyName(): void
    {
        $data = new VisitorRegistrationData();
        $data->name = '';
        $data->mobile = '13800138000';
        $data->company = '测试公司';
        $data->reason = '商务洽谈';
        $data->appointmentTime = new \DateTime('+1 day');

        $this->expectException(InvalidVisitorDataException::class);

        $this->service->validateRegistrationData($data);
    }

    public function testValidateMobileFormat(): void
    {
        // 有效手机号
        $this->assertTrue($this->service->validateMobileFormat('13800138000'));
        $this->assertTrue($this->service->validateMobileFormat('15912345678'));
        $this->assertTrue($this->service->validateMobileFormat('18888888888'));

        // 无效手机号
        $this->assertFalse($this->service->validateMobileFormat('12800138000')); // 错误前缀
        $this->assertFalse($this->service->validateMobileFormat('1380013800')); // 长度不足
        $this->assertFalse($this->service->validateMobileFormat('138001380000')); // 长度过长
        $this->assertFalse($this->service->validateMobileFormat('138001380ab')); // 包含字母
    }

    public function testValidateVehicleNumber(): void
    {
        // null 应该被允许
        $this->assertTrue($this->service->validateVehicleNumber(null));

        // 有效车牌号
        $this->assertTrue($this->service->validateVehicleNumber('京A12345'));
        $this->assertTrue($this->service->validateVehicleNumber('粤B88888'));

        // 空字符串应该被拒绝
        $this->assertFalse($this->service->validateVehicleNumber(''));

        // 过长的车牌号应该被拒绝
        $longVehicleNumber = str_repeat('长', 21); // 21个中文字符
        $this->assertFalse($this->service->validateVehicleNumber($longVehicleNumber));
    }

    public function testValidateAppointmentTime(): void
    {
        // 未来时间应该有效
        $futureTime = new \DateTime('+1 day');
        $this->assertTrue($this->service->validateAppointmentTime($futureTime));

        // 过去时间应该无效
        $pastTime = new \DateTime('-1 day');
        $this->assertFalse($this->service->validateAppointmentTime($pastTime));
    }

    public function testValidateApprovalPermission(): void
    {
        // 有审批权限的用户
        $approverUser = $this->createMockUser(['ROLE_VISITOR_APPROVER']);
        $this->assertTrue($this->service->validateApprovalPermission($approverUser));

        // 无审批权限的用户
        $normalUser = $this->createMockUser(['ROLE_USER']);
        $this->assertFalse($this->service->validateApprovalPermission($normalUser));
    }

    public function testValidateSearchCriteria(): void
    {
        $criteria = new VisitorSearchCriteria();
        $criteria->page = 1;
        $criteria->limit = 10;

        // 基本有效条件
        $this->assertTrue($this->service->validateSearchCriteria($criteria));

        // 无效分页参数
        $criteria->page = 0;
        $this->assertFalse($this->service->validateSearchCriteria($criteria));

        $criteria->page = 1;
        $criteria->limit = 0;
        $this->assertFalse($this->service->validateSearchCriteria($criteria));

        // 有效日期范围
        $criteria->limit = 10;
        $criteria->appointmentFrom = new \DateTime('2024-01-01');
        $criteria->appointmentTo = new \DateTime('2024-01-31');
        $this->assertTrue($this->service->validateSearchCriteria($criteria));

        // 无效日期范围（开始时间晚于结束时间）
        $criteria->appointmentFrom = new \DateTime('2024-01-31');
        $criteria->appointmentTo = new \DateTime('2024-01-01');
        $this->assertFalse($this->service->validateSearchCriteria($criteria));
    }

    public function testValidateVisitorExists(): void
    {
        // 创建并持久化一个访客来测试
        $data = new VisitorRegistrationData();
        $data->name = '存在的访客';
        $data->mobile = '13800138000';
        $data->company = '测试公司';
        $data->reason = '商务洽谈';
        $data->appointmentTime = new \DateTime('+1 day');

        $visitorService = self::getService(VisitorService::class);
        $visitor = $visitorService->registerVisitor($data);

        // 存在的访客
        $visitorId = $visitor->getId();
        $this->assertNotNull($visitorId);
        $this->assertTrue($this->service->validateVisitorExists($visitorId));

        // 不存在的访客
        $this->assertFalse($this->service->validateVisitorExists(99999));
    }

    /**
     * @param array<string> $roles
     */
    private function createMockUser(array $roles): object
    {
        return new class($roles) {
            /** @var array<string> */
            private array $roles;

            /**
             * @param array<string> $roles
             */
            public function __construct(array $roles)
            {
                $this->roles = $roles;
            }

            public function hasRole(string $role): bool
            {
                return in_array($role, $this->roles, true);
            }
        };
    }
}
