<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\EventListener;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractEventSubscriberTestCase;
use Tourze\VisitorManageBundle\Event\PassUsedEvent;
use Tourze\VisitorManageBundle\EventListener\PassEventSubscriber;

/**
 * PassEventSubscriber 测试
 * @internal
 */
#[CoversClass(PassEventSubscriber::class)]
#[RunTestsInSeparateProcesses]
final class PassEventSubscriberTest extends AbstractEventSubscriberTestCase
{
    protected function onSetUp(): void
    {
        // 基础设置
    }

    protected function createEventSubscriber(): PassEventSubscriber
    {
        return self::getService(PassEventSubscriber::class);
    }

    public function testGetSubscribedEventsReturnsExpectedEvents(): void
    {
        $events = PassEventSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(PassUsedEvent::class, $events);
        $this->assertSame('onPassUsed', $events[PassUsedEvent::class]);
    }

    public function testOnPassUsedExecutesWithoutException(): void
    {
        $event = new PassUsedEvent(
            passId: 123,
            passCode: 'ABC123',
            visitorId: 456,
            operatorId: 789
        );

        $subscriber = $this->createEventSubscriber();

        // 测试方法执行不抛出异常
        $this->expectNotToPerformAssertions();
        $subscriber->onPassUsed($event);
    }

    public function testHandleAsyncPassUsedReturnsBoolean(): void
    {
        $event = new PassUsedEvent(
            passId: 123,
            passCode: 'ABC123',
            visitorId: 456,
            operatorId: 789
        );

        $subscriber = $this->createEventSubscriber();
        $result = $subscriber->handleAsyncPassUsed($event);

        // 验证返回值是布尔类型（可能是true或false，取决于日志服务是否正常工作）
        $this->assertIsBool($result, 'handleAsyncPassUsed should return a boolean value');
    }

    public function testPassEventDataIntegrity(): void
    {
        $event = new PassUsedEvent(
            passId: 123,
            passCode: 'ABC123',
            visitorId: 456,
            operatorId: 789
        );

        $this->assertSame(123, $event->getPassId());
        $this->assertSame('ABC123', $event->getPassCode());
        $this->assertSame(456, $event->getVisitorId());
        $this->assertSame(789, $event->getOperatorId());
        $this->assertSame('visitor.pass.used', $event->getEventName());
    }

    public function testSubscriberInstantiation(): void
    {
        $subscriber = $this->createEventSubscriber();

        $this->assertInstanceOf(PassEventSubscriber::class, $subscriber);
    }

    public function testEventHandlingWithDifferentData(): void
    {
        $event = new PassUsedEvent(
            passId: 999,
            passCode: 'XYZ789',
            visitorId: 888,
            operatorId: 777
        );

        $subscriber = $this->createEventSubscriber();

        // 验证事件处理不抛出异常
        $this->expectNotToPerformAssertions();
        $subscriber->onPassUsed($event);
    }
}
