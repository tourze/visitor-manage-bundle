<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Entity\VisitorApproval;
use Tourze\VisitorManageBundle\Enum\ApprovalStatus;
use Tourze\VisitorManageBundle\Enum\VisitorStatus;
use Tourze\VisitorManageBundle\Repository\VisitorApprovalRepository;

/**
 * @internal
 */
#[CoversClass(VisitorApprovalRepository::class)]
#[RunTestsInSeparateProcesses]
final class VisitorApprovalRepositoryTest extends AbstractRepositoryTestCase
{
    private VisitorApprovalRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(VisitorApprovalRepository::class);
    }

    protected function getRepository(): VisitorApprovalRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): object
    {
        $approval = new VisitorApproval();
        $visitor = new Visitor();

        // 设置最少必需的字段
        $visitor->setName('Test Visitor');
        $visitor->setMobile('13800138000');
        $visitor->setCompany('Test Company');
        $visitor->setReason('Test Reason');
        $visitor->setAppointmentTime(new \DateTimeImmutable('tomorrow'));
        $visitor->setStatus(VisitorStatus::PENDING);

        $approval->setVisitor($visitor);
        $approval->setStatus(ApprovalStatus::PENDING);

        return $approval;
    }

    public function testSave(): void
    {
        $approval = $this->createVisitorApproval([], false);

        $this->repository->save($approval, true);

        $this->assertNotNull($approval->getId());
        $found = $this->repository->find($approval->getId());
        $this->assertInstanceOf(VisitorApproval::class, $found);
    }

    public function testRemove(): void
    {
        $approval = $this->createVisitorApproval();
        $this->persistAndFlush($approval);
        $approvalId = $approval->getId();

        $this->repository->remove($approval, true);

        $found = $this->repository->find($approvalId);
        $this->assertNull($found);
    }

    public function testFindByApprover(): void
    {
        $approval = $this->createVisitorApproval(['approver' => 123]);
        $this->persistAndFlush($approval);

        $result = $this->repository->findByApprover(123);

        // 断言结果
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        // 确保我们刚创建的记录在结果中
        $found = false;
        foreach ($result as $item) {
            if ($item === $approval) {
                $found = true;
                $this->assertEquals(123, $item->getApprover());
                break;
            }
        }
        $this->assertTrue($found, 'Created approval should be in the results');

        // 确保所有结果都有正确的审批者
        foreach ($result as $item) {
            $this->assertEquals(123, $item->getApprover());
        }
    }

    public function testFindByStatus(): void
    {
        $approval = $this->createVisitorApproval(['status' => ApprovalStatus::PENDING]);
        $this->persistAndFlush($approval);

        $result = $this->repository->findByStatus(ApprovalStatus::PENDING->value);

        // 断言结果
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        // 确保我们刚创建的记录在结果中
        $found = false;
        foreach ($result as $item) {
            if ($item === $approval) {
                $found = true;
                $this->assertEquals(ApprovalStatus::PENDING, $item->getStatus());
                break;
            }
        }
        $this->assertTrue($found, 'Created approval should be in the results');

        // 确保所有结果都有正确的状态
        foreach ($result as $item) {
            $this->assertEquals(ApprovalStatus::PENDING, $item->getStatus());
        }
    }

    public function testFindByDateRange(): void
    {
        $approval = $this->createVisitorApproval();
        $this->persistAndFlush($approval);

        $startDate = new \DateTime('-1 day');
        $endDate = new \DateTime('+1 day');
        $result = $this->repository->findByDateRange($startDate, $endDate);

        // 断言结果
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        // 确保我们刚创建的记录在结果中
        $found = false;
        foreach ($result as $item) {
            if ($item === $approval) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Created approval should be in the results');

        // 验证所有结果的创建时间都在指定范围内
        foreach ($result as $item) {
            $this->assertGreaterThanOrEqual($startDate, $item->getCreateTime());
            $this->assertLessThanOrEqual($endDate, $item->getCreateTime());
        }
    }

    /**
     * 创建测试用的 Visitor 实体
     * @param array<string, mixed> $attributes
     */
    private function createVisitor(array $attributes = []): Visitor
    {
        /** @var int $counter */
        static $counter = 0;
        $visitor = new Visitor();

        $uniqueValue = (int) (microtime(true) * 1000000) + (++$counter);

        $attributes = array_merge([
            'name' => '测试访客' . $uniqueValue,
            'mobile' => '1380' . str_pad((string) ($uniqueValue % 10000000), 7, '0', STR_PAD_LEFT),
            'company' => '测试公司' . $uniqueValue,
            'reason' => '测试来访' . $uniqueValue,
            'appointmentTime' => new \DateTimeImmutable('tomorrow'),
            'status' => VisitorStatus::PENDING,
        ], $attributes);

        /** @var string $name */
        $name = $attributes['name'];
        /** @var string $mobile */
        $mobile = $attributes['mobile'];
        /** @var string $company */
        $company = $attributes['company'];
        /** @var string $reason */
        $reason = $attributes['reason'];
        /** @var \DateTimeImmutable $appointmentTime */
        $appointmentTime = $attributes['appointmentTime'];
        /** @var VisitorStatus $status */
        $status = $attributes['status'];

        $visitor->setName($name);
        $visitor->setMobile($mobile);
        $visitor->setCompany($company);
        $visitor->setReason($reason);
        $visitor->setAppointmentTime($appointmentTime);
        $visitor->setStatus($status);

        return $visitor;
    }

    /**
     * 创建测试用的 VisitorApproval 实体
     * @param array<string, mixed> $attributes
     */
    private function createVisitorApproval(array $attributes = [], bool $persist = true): VisitorApproval
    {
        $approval = new VisitorApproval();

        // 当不持久化时，创建一个独立的visitor但不保存
        $visitor = $attributes['visitor'] ?? $this->createVisitor();
        $this->assertInstanceOf(Visitor::class, $visitor);

        $attributes = array_merge([
            'visitor' => $visitor,
            'status' => ApprovalStatus::PENDING,
        ], $attributes);

        /** @var Visitor $visitorEntity */
        $visitorEntity = $attributes['visitor'];
        /** @var ApprovalStatus $status */
        $status = $attributes['status'];

        $approval->setVisitor($visitorEntity);
        $approval->setStatus($status);

        if (isset($attributes['approver'])) {
            /** @var int $approver */
            $approver = $attributes['approver'];
            $approval->setApprover($approver);
        }
        if (isset($attributes['rejectReason'])) {
            /** @var string $rejectReason */
            $rejectReason = $attributes['rejectReason'];
            $approval->setRejectReason($rejectReason);
        }
        if (isset($attributes['approveTime'])) {
            /** @var \DateTimeImmutable $approveTime */
            $approveTime = $attributes['approveTime'];
            $approval->setApproveTime($approveTime);
        }

        if ($persist) {
            // 确保visitor先被持久化
            self::getEntityManager()->persist($visitorEntity);
            $this->persistAndFlush($approval);
        }

        return $approval;
    }
}
