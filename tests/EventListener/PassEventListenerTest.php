<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\EventListener;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractEventSubscriberTestCase;
use Tourze\VisitorManageBundle\Entity\VisitorLog;
use Tourze\VisitorManageBundle\Event\PassUsedEvent;
use Tourze\VisitorManageBundle\EventListener\PassEventSubscriber;
use Tourze\VisitorManageBundle\Service\VisitorLogService;

/**
 * @internal
 */
#[CoversClass(PassEventSubscriber::class)]
#[RunTestsInSeparateProcesses]
class PassEventListenerTest extends AbstractEventSubscriberTestCase
{
    private PassEventSubscriber $listener;

    private VisitorLogService $logService;

    private static int $logEventActionCallCount = 0;

    private static int $logErrorCallCount = 0;

    private static bool $shouldThrowOnLogEventAction = false;

    protected function onSetUp(): void
    {
        // 重置静态计数器
        self::$logEventActionCallCount = 0;
        self::$logErrorCallCount = 0;
        self::$shouldThrowOnLogEventAction = false;

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
                // 检查错误消息包含"异常"或"失败"
                self::assertTrue(
                    str_contains($errorMessage, '异常') || str_contains($errorMessage, '失败'),
                    "Error message should contain '异常' or '失败': " . $errorMessage
                );

                // 创建并返回mock VisitorLog
                return $this->createMock(VisitorLog::class);
            })
        ;

        // 使用服务容器获取PassEventSubscriber实例
        self::getContainer()->set(VisitorLogService::class, $this->logService);
        $this->listener = self::getService(PassEventSubscriber::class);
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

    private function shouldThrowOnLogEventAction(): void
    {
        self::$shouldThrowOnLogEventAction = true;
    }

    private function shouldThrowRuntimeException(): void
    {
        self::$shouldThrowOnLogEventAction = true;
    }

    public function testOnPassUsed(): void
    {
        $passId = 123;
        $passCode = 'ABCD1234';
        $visitorId = 456;
        $operatorId = 789;

        $event = new PassUsedEvent($passId, $passCode, $visitorId, $operatorId);

        $initialCallCount = $this->getLogEventActionCallCount();

        $this->listener->onPassUsed($event);

        $this->assertSame($initialCallCount + 1, $this->getLogEventActionCallCount());
    }

    public function testOnPassUsedWithException(): void
    {
        $passId = 999;
        $passCode = 'ERROR123';
        $visitorId = 888;
        $operatorId = 777;

        $event = new PassUsedEvent($passId, $passCode, $visitorId, $operatorId);

        // 设置服务抛出异常
        $this->shouldThrowOnLogEventAction();

        $initialErrorCallCount = $this->getLogErrorCallCount();

        $this->listener->onPassUsed($event);

        $this->assertSame($initialErrorCallCount + 1, $this->getLogErrorCallCount());
    }

    public function testEventSubscriptions(): void
    {
        $subscriptions = PassEventSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(PassUsedEvent::class, $subscriptions);
        $this->assertEquals('onPassUsed', $subscriptions[PassUsedEvent::class]);
    }

    public function testPassCodeValidation(): void
    {
        $passId = 555;
        $passCode = 'VALID123';
        $visitorId = 666;
        $operatorId = 777;

        $event = new PassUsedEvent($passId, $passCode, $visitorId, $operatorId);

        $initialCallCount = $this->getLogEventActionCallCount();

        $this->listener->onPassUsed($event);

        $this->assertSame($initialCallCount + 1, $this->getLogEventActionCallCount());
    }

    public function testHandleAsyncPassUsed(): void
    {
        $event = new PassUsedEvent(1, 'ASYNC123', 2, 3);

        $initialCallCount = $this->getLogEventActionCallCount();

        $result = $this->listener->handleAsyncPassUsed($event);

        $this->assertTrue($result);
        $this->assertSame($initialCallCount + 1, $this->getLogEventActionCallCount());
    }

    public function testHandleAsyncPassUsedWithException(): void
    {
        $event = new PassUsedEvent(1, 'ASYNC123', 2, 3);

        // 设置服务抛出RuntimeException
        $this->shouldThrowRuntimeException();

        $initialErrorCallCount = $this->getLogErrorCallCount();

        $result = $this->listener->handleAsyncPassUsed($event);

        $this->assertFalse($result);
        $this->assertSame($initialErrorCallCount + 1, $this->getLogErrorCallCount());
    }
}
