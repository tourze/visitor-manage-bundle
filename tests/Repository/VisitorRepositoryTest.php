<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Enum\VisitorStatus;
use Tourze\VisitorManageBundle\Repository\VisitorRepository;

/**
 * @internal
 */
#[CoversClass(VisitorRepository::class)]
#[RunTestsInSeparateProcesses]
final class VisitorRepositoryTest extends AbstractRepositoryTestCase
{
    private VisitorRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(VisitorRepository::class);
    }

    protected function getRepository(): VisitorRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): object
    {
        return $this->createVisitor([], false);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function createVisitor(array $attributes = [], bool $persist = true): Visitor
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
            'createTime' => new \DateTimeImmutable(),
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
        /** @var \DateTimeImmutable $createTime */
        $createTime = $attributes['createTime'];

        $visitor->setName($name);
        $visitor->setMobile($mobile);
        $visitor->setCompany($company);
        $visitor->setReason($reason);
        $visitor->setAppointmentTime($appointmentTime);
        $visitor->setStatus($status);
        $visitor->setCreateTime($createTime);

        if ($persist) {
            $this->persistAndFlush($visitor);
        }

        return $visitor;
    }

    public function testCountByDateRangeAndStatus(): void
    {
        $startDate = new \DateTime('-3 days');
        $endDate = new \DateTime('-1 day');

        $this->createVisitor([
            'createTime' => new \DateTimeImmutable('-2 days'),
            'status' => VisitorStatus::PENDING,
        ]);
        $this->createVisitor([
            'createTime' => new \DateTimeImmutable('-2 days'),
            'status' => VisitorStatus::APPROVED,
        ]);
        $this->createVisitor([
            'createTime' => new \DateTimeImmutable('-5 days'),
            'status' => VisitorStatus::PENDING,
        ]);

        $result = $this->repository->countByDateRangeAndStatus($startDate, $endDate, VisitorStatus::PENDING);

        $this->assertGreaterThanOrEqual(1, $result);
    }

    public function testCountByStatus(): void
    {
        $this->createVisitor(['status' => VisitorStatus::PENDING]);
        $this->createVisitor(['status' => VisitorStatus::PENDING]);
        $this->createVisitor(['status' => VisitorStatus::APPROVED]);

        $result = $this->repository->countByStatus(VisitorStatus::PENDING);

        $this->assertGreaterThanOrEqual(2, $result);
    }

    public function testFindByAppointmentDateRange(): void
    {
        $startDate = new \DateTime('+1 day');
        $endDate = new \DateTime('+3 days');

        $this->createVisitor(['appointmentTime' => new \DateTimeImmutable('+2 days')]);
        $this->createVisitor(['appointmentTime' => new \DateTimeImmutable('+5 days')]);
        $this->createVisitor(['appointmentTime' => new \DateTimeImmutable('today')]);

        $result = $this->repository->findByAppointmentDateRange($startDate, $endDate);

        $this->assertGreaterThanOrEqual(1, count($result));
        $this->assertInstanceOf(Visitor::class, $result[0]);
    }

    public function testFindByDate(): void
    {
        $testDate = new \DateTime('-1 day');

        $this->createVisitor(['createTime' => new \DateTimeImmutable('-1 day 10:00:00')]);
        $this->createVisitor(['createTime' => new \DateTimeImmutable('-1 day 15:00:00')]);
        $this->createVisitor(['createTime' => new \DateTimeImmutable('-2 days')]);

        $result = $this->repository->findByDate($testDate);

        $this->assertGreaterThanOrEqual(2, count($result));
        foreach ($result as $visitor) {
            $this->assertInstanceOf(Visitor::class, $visitor);
        }
    }

    public function testFindByDateRange(): void
    {
        $startDate = new \DateTime('-3 days');
        $endDate = new \DateTime('-1 day');

        $this->createVisitor(['createTime' => new \DateTimeImmutable('-2 days')]);
        $this->createVisitor(['createTime' => new \DateTimeImmutable('-5 days')]);
        $this->createVisitor(['createTime' => new \DateTimeImmutable('today')]);

        $result = $this->repository->findByDateRange($startDate, $endDate);

        $this->assertGreaterThanOrEqual(1, count($result));
        $this->assertInstanceOf(Visitor::class, $result[0]);
    }

    public function testFindByMultipleCriteria(): void
    {
        $this->createVisitor([
            'name' => '张三',
            'mobile' => '13800000001',
            'company' => '测试公司A',
            'status' => VisitorStatus::PENDING,
        ]);
        $this->createVisitor([
            'name' => '李四',
            'mobile' => '13800000002',
            'company' => '测试公司B',
            'status' => VisitorStatus::APPROVED,
        ]);

        $result = $this->repository->findByMultipleCriteria(
            name: '张',
            status: VisitorStatus::PENDING,
            page: 1,
            limit: 10
        );

        $this->assertGreaterThanOrEqual(1, count($result));
        $this->assertInstanceOf(Visitor::class, $result[0]);
        $this->assertStringContainsString('张', $result[0]->getName());
    }

    public function testFindByStatus(): void
    {
        $this->createVisitor(['status' => VisitorStatus::PENDING]);
        $this->createVisitor(['status' => VisitorStatus::PENDING]);
        $this->createVisitor(['status' => VisitorStatus::APPROVED]);

        $result = $this->repository->findByStatus(VisitorStatus::PENDING);

        $this->assertGreaterThanOrEqual(2, count($result));
        foreach ($result as $visitor) {
            $this->assertInstanceOf(Visitor::class, $visitor);
            $this->assertSame(VisitorStatus::PENDING, $visitor->getStatus());
        }
    }

    public function testFindPendingApprovals(): void
    {
        $this->createVisitor(['status' => VisitorStatus::PENDING]);
        $this->createVisitor(['status' => VisitorStatus::PENDING]);
        $this->createVisitor(['status' => VisitorStatus::APPROVED]);

        $result = $this->repository->findPendingApprovals();

        $this->assertGreaterThanOrEqual(2, count($result));
        foreach ($result as $visitor) {
            $this->assertInstanceOf(Visitor::class, $visitor);
            $this->assertSame(VisitorStatus::PENDING, $visitor->getStatus());
        }
    }

    public function testFindWithPagination(): void
    {
        $this->createVisitor();
        $this->createVisitor();
        $this->createVisitor();

        $result = $this->repository->findWithPagination(1, 2);

        $this->assertGreaterThanOrEqual(2, count($result));
        foreach ($result as $visitor) {
            $this->assertInstanceOf(Visitor::class, $visitor);
        }
    }
}
