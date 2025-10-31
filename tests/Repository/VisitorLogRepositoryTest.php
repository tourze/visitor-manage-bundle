<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Entity\VisitorLog;
use Tourze\VisitorManageBundle\Enum\VisitorAction;
use Tourze\VisitorManageBundle\Enum\VisitorStatus;
use Tourze\VisitorManageBundle\Repository\VisitorLogRepository;

/**
 * @internal
 */
#[CoversClass(VisitorLogRepository::class)]
#[RunTestsInSeparateProcesses]
final class VisitorLogRepositoryTest extends AbstractRepositoryTestCase
{
    private VisitorLogRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(VisitorLogRepository::class);
    }

    protected function getRepository(): VisitorLogRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): object
    {
        return $this->createVisitorLog([], false);
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

        if ($persist) {
            $this->persistAndFlush($visitor);
        }

        return $visitor;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function createVisitorLog(array $attributes = [], bool $persist = true): VisitorLog
    {
        /** @var int $counter */
        static $counter = 0;
        $log = new VisitorLog();

        $uniqueValue = (int) (microtime(true) * 1000000) + (++$counter);

        // 当不持久化时，创建一个独立的visitor但不保存
        $visitor = $attributes['visitor'] ?? $this->createVisitor([], false);
        $this->assertInstanceOf(Visitor::class, $visitor);

        $attributes = array_merge([
            'visitor' => $visitor,
            'action' => VisitorAction::REGISTERED,
            'remark' => '测试日志' . $uniqueValue,
            'createTime' => new \DateTimeImmutable(),
        ], $attributes);

        /** @var Visitor $visitorEntity */
        $visitorEntity = $attributes['visitor'];
        /** @var VisitorAction $action */
        $action = $attributes['action'];
        /** @var string $remark */
        $remark = $attributes['remark'];
        /** @var \DateTimeImmutable $createTime */
        $createTime = $attributes['createTime'];

        $log->setVisitor($visitorEntity);
        $log->setAction($action);
        $log->setRemark($remark);
        $log->setCreateTime($createTime);

        if (isset($attributes['operator'])) {
            /** @var int $operator */
            $operator = $attributes['operator'];
            $log->setOperator($operator);
        }

        if ($persist) {
            // 确保visitor先被持久化
            self::getEntityManager()->persist($visitorEntity);
            $this->persistAndFlush($log);
        }

        return $log;
    }

    public function testCountByAction(): void
    {
        $this->createVisitorLog(['action' => VisitorAction::REGISTERED]);
        $this->createVisitorLog(['action' => VisitorAction::REGISTERED]);
        $this->createVisitorLog(['action' => VisitorAction::APPROVED]);

        $result = $this->repository->countByAction(VisitorAction::REGISTERED);

        $this->assertGreaterThanOrEqual(2, $result);
    }

    public function testFindByAction(): void
    {
        $this->createVisitorLog(['action' => VisitorAction::REGISTERED]);
        $this->createVisitorLog(['action' => VisitorAction::REGISTERED]);
        $this->createVisitorLog(['action' => VisitorAction::APPROVED]);

        $result = $this->repository->findByAction(VisitorAction::REGISTERED);

        $this->assertGreaterThanOrEqual(2, count($result));
        foreach ($result as $log) {
            $this->assertInstanceOf(VisitorLog::class, $log);
            $this->assertSame(VisitorAction::REGISTERED, $log->getAction());
        }
    }

    public function testFindByDateRange(): void
    {
        $startDate = new \DateTime('-3 days');
        $endDate = new \DateTime('-1 day');

        $this->createVisitorLog(['createTime' => new \DateTimeImmutable('-2 days')]);
        $this->createVisitorLog(['createTime' => new \DateTimeImmutable('-5 days')]);
        $this->createVisitorLog(['createTime' => new \DateTimeImmutable('today')]);

        $result = $this->repository->findByDateRange($startDate, $endDate);

        $this->assertGreaterThanOrEqual(1, count($result));
        if (count($result) > 0) {
            $this->assertInstanceOf(VisitorLog::class, $result[0]);
        }
    }

    public function testFindByOperator(): void
    {
        $this->createVisitorLog(['operator' => 2001]);
        $this->createVisitorLog(['operator' => 2001]);
        $this->createVisitorLog(['operator' => 2002]);

        $result = $this->repository->findByOperator(2001);

        $this->assertGreaterThanOrEqual(2, count($result));
        foreach ($result as $log) {
            $this->assertInstanceOf(VisitorLog::class, $log);
            $this->assertSame(2001, $log->getOperator());
        }
    }

    public function testFindByVisitor(): void
    {
        $visitor1 = $this->createVisitor();
        $visitor2 = $this->createVisitor();

        $this->createVisitorLog(['visitor' => $visitor1]);
        $this->createVisitorLog(['visitor' => $visitor1]);
        $this->createVisitorLog(['visitor' => $visitor2]);

        $result = $this->repository->findByVisitor($visitor1);

        $this->assertGreaterThanOrEqual(2, count($result));
        foreach ($result as $log) {
            $this->assertInstanceOf(VisitorLog::class, $log);
            $this->assertSame($visitor1->getId(), $log->getVisitor()?->getId());
        }
    }
}
