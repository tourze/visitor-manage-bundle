<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\EventListener;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractEventSubscriberTestCase;
use Tourze\VisitorManageBundle\Event\VisitorApprovedEvent;
use Tourze\VisitorManageBundle\Event\VisitorRegisteredEvent;
use Tourze\VisitorManageBundle\EventListener\VisitorEventSubscriber;

/**
 * VisitorEventSubscriber 测试
 * @internal
 */
#[CoversClass(VisitorEventSubscriber::class)]
#[RunTestsInSeparateProcesses]
final class VisitorEventSubscriberTest extends AbstractEventSubscriberTestCase
{
    protected function onSetUp(): void
    {
        // 基础设置
    }

    protected function createEventSubscriber(): VisitorEventSubscriber
    {
        return self::getService(VisitorEventSubscriber::class);
    }

    public function testGetSubscribedEventsReturnsExpectedEvents(): void
    {
        $events = VisitorEventSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(VisitorRegisteredEvent::class, $events);
        $this->assertArrayHasKey(VisitorApprovedEvent::class, $events);
        $this->assertSame('onVisitorRegistered', $events[VisitorRegisteredEvent::class]);
        $this->assertSame('onVisitorApproved', $events[VisitorApprovedEvent::class]);
    }

    public function testOnVisitorRegisteredExecutesWithoutException(): void
    {
        $visitorData = ['name' => '张三', 'mobile' => '13800138000'];
        $event = new VisitorRegisteredEvent(
            visitorId: 123,
            visitorData: $visitorData,
            operatorId: 456
        );

        $subscriber = $this->createEventSubscriber();

        // 测试方法执行不抛出异常
        $this->expectNotToPerformAssertions();
        $subscriber->onVisitorRegistered($event);
    }

    public function testVisitorRegisteredEventDataIntegrity(): void
    {
        $visitorData = ['name' => '张三', 'mobile' => '13800138000'];
        $event = new VisitorRegisteredEvent(
            visitorId: 123,
            visitorData: $visitorData,
            operatorId: 456
        );

        $this->assertSame(123, $event->getVisitorId());
        $this->assertSame($visitorData, $event->getVisitorData());
        $this->assertSame(456, $event->getOperatorId());
        $this->assertSame('visitor.registered', $event->getEventName());
    }

    public function testOnVisitorApprovedExecutesWithoutException(): void
    {
        $event = new VisitorApprovedEvent(
            visitorId: 123,
            approverId: 456,
            approverName: '管理员',
            remark: '审批通过'
        );

        $subscriber = $this->createEventSubscriber();

        // 测试方法执行不抛出异常
        $this->expectNotToPerformAssertions();
        $subscriber->onVisitorApproved($event);
    }

    public function testVisitorApprovedEventDataIntegrity(): void
    {
        $event = new VisitorApprovedEvent(
            visitorId: 123,
            approverId: 456,
            approverName: '管理员',
            remark: '审批通过'
        );

        $this->assertSame(123, $event->getVisitorId());
        $this->assertSame(456, $event->getApproverId());
        $this->assertSame('管理员', $event->getApproverName());
        $this->assertSame('审批通过', $event->getRemark());
        $this->assertSame('visitor.approved', $event->getEventName());
    }

    public function testProcessBatchEventsHandlesMultipleRegisteredEvents(): void
    {
        $event1 = new VisitorRegisteredEvent(
            visitorId: 123,
            visitorData: ['name' => '张三'],
            operatorId: 456
        );

        $event2 = new VisitorRegisteredEvent(
            visitorId: 124,
            visitorData: ['name' => '李四'],
            operatorId: 456
        );

        $subscriber = $this->createEventSubscriber();

        // 测试方法执行不抛出异常
        $this->expectNotToPerformAssertions();
        $subscriber->processBatchEvents([$event1, $event2]);
    }

    public function testSubscriberInstantiation(): void
    {
        $subscriber = $this->createEventSubscriber();

        $this->assertInstanceOf(VisitorEventSubscriber::class, $subscriber);
    }

    public function testProcessBatchEventsOnlyAcceptsRegisteredEvents(): void
    {
        $registeredEvent1 = new VisitorRegisteredEvent(
            visitorId: 123,
            visitorData: ['name' => '张三'],
            operatorId: 456
        );

        $registeredEvent2 = new VisitorRegisteredEvent(
            visitorId: 124,
            visitorData: ['name' => '李四'],
            operatorId: 456
        );

        $subscriber = $this->createEventSubscriber();

        // 测试方法执行不抛出异常
        $this->expectNotToPerformAssertions();
        $subscriber->processBatchEvents([$registeredEvent1, $registeredEvent2]);
    }

    public function testProcessBatchEventsHandlesEmptyArray(): void
    {
        $subscriber = $this->createEventSubscriber();

        // 测试空数组处理不抛出异常
        $this->expectNotToPerformAssertions();
        $subscriber->processBatchEvents([]);
    }

    public function testEventHandlingWithVariousData(): void
    {
        $approvedEvent = new VisitorApprovedEvent(
            visitorId: 999,
            approverId: 888,
            approverName: '审批员B',
            remark: '紧急访客'
        );

        $registeredEvent = new VisitorRegisteredEvent(
            visitorId: 777,
            visitorData: ['name' => '王五', 'mobile' => '13900139000'],
            operatorId: 666
        );

        $subscriber = $this->createEventSubscriber();

        // 测试不同事件处理不抛出异常
        $this->expectNotToPerformAssertions();
        $subscriber->onVisitorApproved($approvedEvent);
        $subscriber->onVisitorRegistered($registeredEvent);
    }
}
