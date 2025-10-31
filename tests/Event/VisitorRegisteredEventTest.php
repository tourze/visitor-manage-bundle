<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\VisitorManageBundle\Event\VisitorRegisteredEvent;

/**
 * @internal
 */
#[CoversClass(VisitorRegisteredEvent::class)]
class VisitorRegisteredEventTest extends TestCase
{
    public function testEventCreation(): void
    {
        $visitorId = 123;
        $visitorData = [
            'name' => '张三',
            'mobile' => '13800138000',
            'company' => '测试公司',
            'reason' => '商务洽谈',
        ];
        $operatorId = 456;

        $event = new VisitorRegisteredEvent($visitorId, $visitorData, $operatorId);

        $this->assertInstanceOf(VisitorRegisteredEvent::class, $event);
        $this->assertEquals($visitorId, $event->getVisitorId());
        $this->assertEquals($visitorData, $event->getVisitorData());
        $this->assertEquals($operatorId, $event->getOperatorId());
        $this->assertInstanceOf(\DateTimeImmutable::class, $event->getOccurredOn());
    }

    public function testEventData(): void
    {
        $visitorId = 789;
        $visitorData = [
            'name' => '李四',
            'mobile' => '13900139000',
            'company' => '另一家公司',
            'reason' => '技术交流',
            'appointmentTime' => '2024-01-15 14:30:00',
        ];
        $operatorId = 999;

        $event = new VisitorRegisteredEvent($visitorId, $visitorData, $operatorId);

        $this->assertEquals('李四', $event->getVisitorData()['name']);
        $this->assertEquals('13900139000', $event->getVisitorData()['mobile']);
        $this->assertEquals('另一家公司', $event->getVisitorData()['company']);
        $this->assertEquals('技术交流', $event->getVisitorData()['reason']);
        $this->assertEquals('2024-01-15 14:30:00', $event->getVisitorData()['appointmentTime']);
    }

    public function testSerialization(): void
    {
        $visitorId = 111;
        $visitorData = ['name' => '王五', 'mobile' => '13700137000'];
        $operatorId = 222;

        $event = new VisitorRegisteredEvent($visitorId, $visitorData, $operatorId);

        $serialized = serialize($event);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(VisitorRegisteredEvent::class, $unserialized);
        $this->assertEquals($event->getVisitorId(), $unserialized->getVisitorId());
        $this->assertEquals($event->getVisitorData(), $unserialized->getVisitorData());
        $this->assertEquals($event->getOperatorId(), $unserialized->getOperatorId());
        $this->assertEquals($event->getOccurredOn()->getTimestamp(), $unserialized->getOccurredOn()->getTimestamp());
    }

    public function testEventName(): void
    {
        $event = new VisitorRegisteredEvent(1, [], 1);

        $this->assertEquals('visitor.registered', $event->getEventName());
    }

    public function testEventVersion(): void
    {
        $event = new VisitorRegisteredEvent(1, [], 1);

        $this->assertEquals('1.0', $event->getVersion());
    }

    public function testToArray(): void
    {
        $visitorId = 333;
        $visitorData = ['name' => '赵六', 'company' => '第三家公司'];
        $operatorId = 444;

        $event = new VisitorRegisteredEvent($visitorId, $visitorData, $operatorId);
        $array = $event->toArray();

        $this->assertEquals($visitorId, $array['visitorId']);
        $this->assertEquals($visitorData, $array['visitorData']);
        $this->assertEquals($operatorId, $array['operatorId']);
        $this->assertArrayHasKey('occurredOn', $array);
    }
}
