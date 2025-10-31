<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\DTO;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\VisitorManageBundle\DTO\VisitorSearchCriteria;
use Tourze\VisitorManageBundle\Enum\VisitorStatus;

/**
 * @internal
 */
#[CoversClass(VisitorSearchCriteria::class)]
class VisitorSearchCriteriaTest extends TestCase
{
    public function testSearchCriteriaCreation(): void
    {
        $criteria = new VisitorSearchCriteria();

        $this->assertInstanceOf(VisitorSearchCriteria::class, $criteria);
        $this->assertNull($criteria->name);
        $this->assertNull($criteria->mobile);
        $this->assertNull($criteria->company);
        $this->assertNull($criteria->appointmentFrom);
        $this->assertNull($criteria->appointmentTo);
        $this->assertNull($criteria->status);
    }

    public function testPaginationDefaults(): void
    {
        $criteria = new VisitorSearchCriteria();

        $this->assertEquals(1, $criteria->page);
        $this->assertEquals(20, $criteria->limit);
    }

    public function testDataBinding(): void
    {
        $criteria = new VisitorSearchCriteria();
        $fromDate = new \DateTime('2024-01-01');
        $toDate = new \DateTime('2024-01-31');

        $criteria->name = '张三';
        $criteria->mobile = '13800138000';
        $criteria->company = '测试公司';
        $criteria->appointmentFrom = $fromDate;
        $criteria->appointmentTo = $toDate;
        $criteria->status = VisitorStatus::APPROVED;
        $criteria->page = 2;
        $criteria->limit = 50;

        $this->assertEquals('张三', $criteria->name);
        $this->assertEquals('13800138000', $criteria->mobile);
        $this->assertEquals('测试公司', $criteria->company);
        $this->assertEquals($fromDate, $criteria->appointmentFrom);
        $this->assertEquals($toDate, $criteria->appointmentTo);
        $this->assertEquals(VisitorStatus::APPROVED, $criteria->status);
        $this->assertEquals(2, $criteria->page);
        $this->assertEquals(50, $criteria->limit);
    }

    public function testDateRangeValidation(): void
    {
        $criteria = new VisitorSearchCriteria();
        $fromDate = new \DateTime('2024-01-31');
        $toDate = new \DateTime('2024-01-01'); // 结束时间早于开始时间

        $criteria->appointmentFrom = $fromDate;
        $criteria->appointmentTo = $toDate;

        // 这里测试数据绑定是否正常，实际业务验证会在Service层处理
        $this->assertEquals($fromDate, $criteria->appointmentFrom);
        $this->assertEquals($toDate, $criteria->appointmentTo);
        $this->assertTrue($criteria->appointmentFrom > $criteria->appointmentTo);
    }

    public function testPaginationBoundaries(): void
    {
        $criteria = new VisitorSearchCriteria();

        // 测试负数和零值
        $criteria->page = -1;
        $criteria->limit = 0;

        $this->assertEquals(-1, $criteria->page);
        $this->assertEquals(0, $criteria->limit);

        // 业务验证会在Repository或Service层处理边界值
    }

    public function testPartialSearch(): void
    {
        $criteria = new VisitorSearchCriteria();

        // 只设置部分搜索条件
        $criteria->name = '张';
        $criteria->status = VisitorStatus::PENDING;
        // 其他字段保持null

        $this->assertEquals('张', $criteria->name);
        $this->assertEquals(VisitorStatus::PENDING, $criteria->status);
        $this->assertNull($criteria->mobile);
        $this->assertNull($criteria->company);
        $this->assertNull($criteria->appointmentFrom);
        $this->assertNull($criteria->appointmentTo);
    }

    public function testAllStatusValues(): void
    {
        $criteria = new VisitorSearchCriteria();
        $statuses = [
            VisitorStatus::PENDING,
            VisitorStatus::APPROVED,
            VisitorStatus::REJECTED,
            VisitorStatus::SIGNED_IN,
            VisitorStatus::SIGNED_OUT,
        ];

        foreach ($statuses as $status) {
            $criteria->status = $status;
            $this->assertEquals($status, $criteria->status);
        }
    }
}
