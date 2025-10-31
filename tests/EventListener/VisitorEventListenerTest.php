<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\EventListener;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractEventSubscriberTestCase;
use Tourze\VisitorManageBundle\Entity\VisitorLog;
use Tourze\VisitorManageBundle\Entity\VisitorPass;
use Tourze\VisitorManageBundle\Event\VisitorApprovedEvent;
use Tourze\VisitorManageBundle\Event\VisitorRegisteredEvent;
use Tourze\VisitorManageBundle\EventListener\VisitorEventSubscriber;
use Tourze\VisitorManageBundle\Service\VisitorLogService;
use Tourze\VisitorManageBundle\Service\VisitorPassService;

/**
 * @internal
 */
#[CoversClass(VisitorEventSubscriber::class)]
#[RunTestsInSeparateProcesses]
class VisitorEventListenerTest extends AbstractEventSubscriberTestCase
{
    private VisitorEventSubscriber $listener;

    private VisitorLogService $logService;

    private VisitorPassService $passService;

    private static int $logEventActionCallCount = 0;

    private static int $logErrorCallCount = 0;

    private static int $autoGenerateCallCount = 0;

    private static bool $shouldThrowOnLogEventAction = false;

    private static bool $shouldThrow = false;

    protected function onSetUp(): void
    {
        // 重置静态计数器
        self::$logEventActionCallCount = 0;
        self::$logErrorCallCount = 0;
        self::$autoGenerateCallCount = 0;
        self::$shouldThrowOnLogEventAction = false;
        self::$shouldThrow = false;

        // 使用PHPUnit Mock系统创建VisitorLogService的mock
        $this->logService = $this->createMock(VisitorLogService::class);

        // 配置logEventAction方法的行为
        $this->logService->method('logEventAction')
            ->willReturnCallback(function (int $visitorId, string $eventName, int $operatorId, string $remark) {
                if (self::$shouldThrowOnLogEventAction) {
                    throw new \Exception('日志记录失败');
                }
                ++self::$logEventActionCallCount;

                // 基本参数验证

                // 创建并返回mock VisitorLog
                return $this->createMock(VisitorLog::class);
            })
        ;

        // 配置logError方法的行为
        $this->logService->method('logError')
            ->willReturnCallback(function ($visitor, string $errorMessage, int $operatorId, array $context = []) {
                ++self::$logErrorCallCount;
                self::assertStringContainsString('失败', $errorMessage);

                // 创建并返回mock VisitorLog
                return $this->createMock(VisitorLog::class);
            })
        ;

        // 使用PHPUnit Mock系统创建VisitorPassService的mock
        $this->passService = $this->createMock(VisitorPassService::class);

        // 配置autoGeneratePassForApprovedVisitor方法的行为
        $this->passService->method('autoGeneratePassForApprovedVisitor')
            ->willReturnCallback(function (int $visitorId, int $approverId) {
                if (self::$shouldThrow) {
                    throw new \Exception('通行码生成失败');
                }
                ++self::$autoGenerateCallCount;

                // 返回一个mock VisitorPass 对象
                return $this->createMock(VisitorPass::class);
            })
        ;

        // 使用服务容器获取VisitorEventSubscriber实例
        self::getContainer()->set(VisitorLogService::class, $this->logService);
        self::getContainer()->set(VisitorPassService::class, $this->passService);
        $this->listener = self::getService(VisitorEventSubscriber::class);
    }

    // 访问静态计数器的方法
    private function getLogEventActionCallCount(): int
    {
        return self::$logEventActionCallCount;
    }

    private function getLogErrorCallCount(): int
    {
        return self::$logErrorCallCount;
    }

    private function getAutoGenerateCallCount(): int
    {
        return self::$autoGenerateCallCount;
    }

    private function shouldThrowOnLogEventAction(): void
    {
        self::$shouldThrowOnLogEventAction = true;
    }

    private function shouldThrowException(): void
    {
        self::$shouldThrow = true;
    }

    public function testOnVisitorRegistered(): void
    {
        $visitorId = 123;
        $operatorId = 456;
        $visitorData = [
            'name' => '张三',
            'mobile' => '13800138000',
            'company' => '测试公司',
        ];

        $event = new VisitorRegisteredEvent($visitorId, $visitorData, $operatorId);

        $initialCallCount = $this->getLogEventActionCallCount();

        $this->listener->onVisitorRegistered($event);

        $this->assertSame($initialCallCount + 1, $this->getLogEventActionCallCount());
    }

    public function testOnVisitorApproved(): void
    {
        $visitorId = 789;
        $approverId = 111;
        $approverName = '审批人';
        $remark = '审批通过';

        $event = new VisitorApprovedEvent($visitorId, $approverId, $approverName, $remark);

        $initialLogCallCount = $this->getLogEventActionCallCount();
        $initialPassCallCount = $this->getAutoGenerateCallCount();

        $this->listener->onVisitorApproved($event);

        $this->assertSame($initialLogCallCount + 1, $this->getLogEventActionCallCount());
        $this->assertSame($initialPassCallCount + 1, $this->getAutoGenerateCallCount());
    }

    public function testOnVisitorApprovedWithPassGenerationFailure(): void
    {
        $visitorId = 999;
        $approverId = 222;
        $approverName = '审批人';
        $remark = '审批通过';

        $event = new VisitorApprovedEvent($visitorId, $approverId, $approverName, $remark);

        // 设置服务抛出异常
        $this->shouldThrowException();

        $initialLogCallCount = $this->getLogEventActionCallCount();
        $initialErrorCallCount = $this->getLogErrorCallCount();

        $this->listener->onVisitorApproved($event);

        $this->assertSame($initialLogCallCount + 1, $this->getLogEventActionCallCount());
        $this->assertSame($initialErrorCallCount + 1, $this->getLogErrorCallCount());
    }

    public function testEventSubscriptions(): void
    {
        $subscriptions = VisitorEventSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(VisitorRegisteredEvent::class, $subscriptions);
        $this->assertArrayHasKey(VisitorApprovedEvent::class, $subscriptions);

        $this->assertEquals('onVisitorRegistered', $subscriptions[VisitorRegisteredEvent::class]);
        $this->assertEquals('onVisitorApproved', $subscriptions[VisitorApprovedEvent::class]);
    }

    public function testProcessBatchEvents(): void
    {
        $events = [
            new VisitorRegisteredEvent(1, ['name' => '访客1'], 100),
            new VisitorRegisteredEvent(2, ['name' => '访客2'], 100),
            new VisitorRegisteredEvent(3, ['name' => '访客3'], 100),
        ];

        $initialCallCount = $this->getLogEventActionCallCount();

        $this->listener->processBatchEvents($events);

        $this->assertSame($initialCallCount + 3, $this->getLogEventActionCallCount());
    }

    public function testEventProcessingWithException(): void
    {
        $visitorId = 555;
        $operatorId = 666;
        $visitorData = ['name' => '异常测试'];

        $event = new VisitorRegisteredEvent($visitorId, $visitorData, $operatorId);

        // 设置服务抛出异常
        $this->shouldThrowOnLogEventAction();

        $initialErrorCallCount = $this->getLogErrorCallCount();

        $this->listener->onVisitorRegistered($event);

        $this->assertSame($initialErrorCallCount + 1, $this->getLogErrorCallCount());
    }
}
