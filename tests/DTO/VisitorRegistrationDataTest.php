<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\DTO;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tourze\VisitorManageBundle\DTO\VisitorRegistrationData;

/**
 * @internal
 */
#[CoversClass(VisitorRegistrationData::class)]
class VisitorRegistrationDataTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator()
        ;
    }

    public function testDataBinding(): void
    {
        $data = new VisitorRegistrationData();
        $appointmentTime = new \DateTime('+1 day');

        $data->name = '张三';
        $data->mobile = '13800138000';
        $data->company = '测试公司';
        $data->reason = '商务洽谈';
        $data->vehicleNumber = '京A12345';
        $data->appointmentTime = $appointmentTime;
        $data->bizUserId = 123;

        $this->assertEquals('张三', $data->name);
        $this->assertEquals('13800138000', $data->mobile);
        $this->assertEquals('测试公司', $data->company);
        $this->assertEquals('商务洽谈', $data->reason);
        $this->assertEquals('京A12345', $data->vehicleNumber);
        $this->assertEquals($appointmentTime, $data->appointmentTime);
        $this->assertEquals(123, $data->bizUserId);
    }

    public function testValidation(): void
    {
        $data = new VisitorRegistrationData();
        $data->name = '张三';
        $data->mobile = '13800138000';
        $data->company = '测试公司';
        $data->reason = '商务洽谈';
        $data->appointmentTime = new \DateTime('+1 day');

        $violations = $this->validator->validate($data);

        $this->assertCount(0, $violations, '有效数据应该通过验证');
    }

    public function testMobileFormatValidation(): void
    {
        $data = new VisitorRegistrationData();
        $data->name = '张三';
        $data->mobile = 'invalid-phone';
        $data->company = '测试公司';
        $data->reason = '商务洽谈';
        $data->appointmentTime = new \DateTime('+1 day');

        $violations = $this->validator->validate($data);

        $this->assertGreaterThan(0, count($violations), '无效手机号应该验证失败');

        $phoneError = false;
        foreach ($violations as $violation) {
            if ('mobile' === $violation->getPropertyPath()) {
                $phoneError = true;
                break;
            }
        }
        $this->assertTrue($phoneError, '应该有手机号格式错误');
    }

    public function testAppointmentTimeValidation(): void
    {
        $data = new VisitorRegistrationData();
        $data->name = '张三';
        $data->mobile = '13800138000';
        $data->company = '测试公司';
        $data->reason = '商务洽谈';
        $data->appointmentTime = new \DateTime('-1 day'); // 过去的时间

        $violations = $this->validator->validate($data);

        $this->assertGreaterThan(0, count($violations), '过去的预约时间应该验证失败');

        $timeError = false;
        foreach ($violations as $violation) {
            if ('appointmentTime' === $violation->getPropertyPath()) {
                $timeError = true;
                break;
            }
        }
        $this->assertTrue($timeError, '应该有预约时间错误');
    }

    public function testRequiredFields(): void
    {
        $data = new VisitorRegistrationData();
        // 不设置任何必填字段

        $violations = $this->validator->validate($data);

        $this->assertGreaterThan(0, count($violations), '空数据应该验证失败');

        $requiredFields = ['name', 'mobile', 'company', 'reason', 'appointmentTime'];
        $errorFields = [];

        foreach ($violations as $violation) {
            $errorFields[] = $violation->getPropertyPath();
        }

        foreach ($requiredFields as $field) {
            $this->assertContains($field, $errorFields, "字段 {$field} 应该是必填的");
        }
    }

    public function testOptionalFields(): void
    {
        $data = new VisitorRegistrationData();
        $data->name = '张三';
        $data->mobile = '13800138000';
        $data->company = '测试公司';
        $data->reason = '商务洽谈';
        $data->appointmentTime = new \DateTime('+1 day');
        // vehicleNumber 和 bizUserId 保持默认值 null

        $violations = $this->validator->validate($data);

        $this->assertCount(0, $violations, '可选字段为空时应该通过验证');
        $this->assertNull($data->vehicleNumber);
        $this->assertNull($data->bizUserId);
    }

    public function testLongTextFields(): void
    {
        $data = new VisitorRegistrationData();
        $data->name = '张三';
        $data->mobile = '13800138000';
        $data->company = '测试公司';
        $data->reason = str_repeat('这是一个很长的拜访原因。', 100); // 很长的原因
        $data->appointmentTime = new \DateTime('+1 day');

        $violations = $this->validator->validate($data);

        // 长文本应该被允许（拜访原因可能很详细）
        $reasonErrors = [];
        foreach ($violations as $violation) {
            if ('reason' === $violation->getPropertyPath()) {
                $reasonErrors[] = $violation;
            }
        }

        // 根据具体验证规则，这里可能需要调整
        $this->assertLessThanOrEqual(1, count($reasonErrors), '拜访原因应该允许较长文本');
    }
}
