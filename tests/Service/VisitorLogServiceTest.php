<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Enum\VisitorAction;
use Tourze\VisitorManageBundle\Enum\VisitorStatus;
use Tourze\VisitorManageBundle\Exception\VisitorNotFoundException;
use Tourze\VisitorManageBundle\Service\VisitorLogService;

/**
 * @internal
 */
#[CoversClass(VisitorLogService::class)]
#[RunTestsInSeparateProcesses]
final class VisitorLogServiceTest extends AbstractIntegrationTestCase
{
    private VisitorLogService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(VisitorLogService::class);
    }

    public function testLogActionSuccess(): void
    {
        $visitor = $this->createVisitor();
        $operatorId = 123;
        $action = VisitorAction::REGISTERED;
        $remark = '访客成功注册';

        $log = $this->service->logAction($visitor, $action, $operatorId, $remark);
        self::assertEquals($visitor, $log->getVisitor());
        self::assertEquals($action, $log->getAction());
        self::assertEquals($operatorId, $log->getOperator());
        self::assertEquals($remark, $log->getRemark());
        self::assertInstanceOf(\DateTimeImmutable::class, $log->getCreateTime());
    }

    public function testLogActionWithoutRemark(): void
    {
        $visitor = $this->createVisitor();
        $operatorId = 456;
        $action = VisitorAction::APPROVED;

        $log = $this->service->logAction($visitor, $action, $operatorId);
        self::assertEquals($visitor, $log->getVisitor());
        self::assertEquals($action, $log->getAction());
        self::assertEquals($operatorId, $log->getOperator());
        self::assertEquals('', $log->getRemark());
    }

    public function testLogErrorWithVisitor(): void
    {
        $visitor = $this->createVisitor();
        $operatorId = 789;
        $errorMessage = '审批过程中发生错误';
        $context = ['error_code' => 'APPROVAL_FAILED', 'details' => 'Network timeout'];

        $log = $this->service->logError($visitor, $errorMessage, $operatorId, $context);
        self::assertEquals($visitor, $log->getVisitor());
        self::assertEquals(VisitorAction::ERROR, $log->getAction());
        self::assertEquals($operatorId, $log->getOperator());
        self::assertStringContainsString($errorMessage, $log->getRemark());
        self::assertStringContainsString('APPROVAL_FAILED', $log->getRemark());
    }

    public function testLogErrorWithoutVisitor(): void
    {
        $operatorId = 999;
        $errorMessage = '系统级错误';
        $context = ['system_error' => true];

        $log = $this->service->logError(null, $errorMessage, $operatorId, $context);
        self::assertNull($log->getVisitor());
        self::assertEquals(VisitorAction::ERROR, $log->getAction());
        self::assertEquals($operatorId, $log->getOperator());
        self::assertStringContainsString($errorMessage, $log->getRemark());
    }

    public function testBatchLogActions(): void
    {
        $visitors = [
            $this->createVisitor(1, '张三'),
            $this->createVisitor(2, '李四'),
            $this->createVisitor(3, '王五'),
        ];
        $action = VisitorAction::BULK_APPROVED;
        $operatorId = 111;
        $remark = '批量审批通过';

        $logs = $this->service->batchLogActions($visitors, $action, $operatorId, $remark);

        self::assertCount(3, $logs);
        foreach ($logs as $i => $log) {
            self::assertEquals($visitors[$i], $log->getVisitor());
            self::assertEquals($action, $log->getAction());
            self::assertEquals($operatorId, $log->getOperator());
            self::assertEquals($remark, $log->getRemark());
        }
    }

    public function testBatchLogActionsEmpty(): void
    {
        $visitors = [];
        $action = VisitorAction::BULK_APPROVED;
        $operatorId = 222;

        $logs = $this->service->batchLogActions($visitors, $action, $operatorId);

        self::assertCount(0, $logs);
    }

    public function testGetVisitorLogs(): void
    {
        $visitor = $this->createVisitor();
        $operatorId = 333;

        $this->service->logAction($visitor, VisitorAction::REGISTERED, $operatorId);
        $this->service->logAction($visitor, VisitorAction::APPROVED, $operatorId);

        $logs = $this->service->getVisitorLogs($visitor);

        self::assertGreaterThanOrEqual(2, count($logs));
    }

    public function testGetLogsByAction(): void
    {
        $visitor = $this->createVisitor();
        $operatorId = 444;
        $action = VisitorAction::APPROVED;

        $this->service->logAction($visitor, $action, $operatorId);

        $logs = $this->service->getLogsByAction($action);

        self::assertGreaterThanOrEqual(1, count($logs));
    }

    public function testGetLogsByOperator(): void
    {
        $visitor = $this->createVisitor();
        $operatorId = 555;

        $this->service->logAction($visitor, VisitorAction::REGISTERED, $operatorId);

        $logs = $this->service->getLogsByOperator($operatorId);

        self::assertGreaterThanOrEqual(1, count($logs));
    }

    public function testCountLogsByAction(): void
    {
        $action = VisitorAction::REGISTERED;

        $count = $this->service->countLogsByAction($action);

        self::assertGreaterThanOrEqual(0, $count);
    }

    public function testLogEventAction(): void
    {
        $visitor = $this->createVisitor();

        // 持久化 visitor 以便测试中能找到
        $em = self::getEntityManager();
        $em->persist($visitor);
        $em->flush();

        $eventName = 'visitor.approved';
        $operatorId = 666;
        $remark = '通过事件系统审批访客';

        $visitorId = $visitor->getId();
        self::assertNotNull($visitorId);

        $log = $this->service->logEventAction($visitorId, $eventName, $operatorId, $remark);
        self::assertEquals($visitorId, $log->getVisitor()?->getId());
        self::assertEquals(VisitorAction::APPROVED, $log->getAction());
        self::assertEquals($operatorId, $log->getOperator());
        self::assertEquals($remark, $log->getRemark());
    }

    public function testLogEventActionVisitorNotFound(): void
    {
        $nonExistentVisitorId = 99999;
        $eventName = 'visitor.registered';
        $operatorId = 777;
        $remark = '测试不存在的访客';

        $this->expectException(VisitorNotFoundException::class);

        $this->service->logEventAction($nonExistentVisitorId, $eventName, $operatorId, $remark);
    }

    private function createVisitor(int $id = 1, string $name = '测试访客'): Visitor
    {
        $visitor = new Visitor();
        $visitor->setName($name);
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
}
