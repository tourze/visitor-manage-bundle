<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\VisitorManageBundle\Event\PassUsedEvent;

/**
 * @internal
 */
#[CoversClass(PassUsedEvent::class)]
class PassUsedEventTest extends TestCase
{
    public function testPassUsageData(): void
    {
        $passId = 123;
        $passCode = 'ABCD1234';
        $visitorId = 456;
        $operatorId = 789;

        $event = new PassUsedEvent($passId, $passCode, $visitorId, $operatorId);

        $this->assertEquals($passId, $event->getPassId());
        $this->assertEquals($passCode, $event->getPassCode());
        $this->assertEquals($visitorId, $event->getVisitorId());
        $this->assertEquals($operatorId, $event->getOperatorId());
        $this->assertInstanceOf(\DateTimeImmutable::class, $event->getOccurredOn());
    }

    public function testTimestampAccuracy(): void
    {
        $beforeCreation = new \DateTimeImmutable();
        $event = new PassUsedEvent(1, 'TEST1234', 2, 3);
        $afterCreation = new \DateTimeImmutable();

        $occurredOn = $event->getOccurredOn();

        $this->assertGreaterThanOrEqual($beforeCreation->getTimestamp(), $occurredOn->getTimestamp());
        $this->assertLessThanOrEqual($afterCreation->getTimestamp(), $occurredOn->getTimestamp());
    }

    public function testEventName(): void
    {
        $event = new PassUsedEvent(1, 'TEST1234', 2, 3);

        $this->assertEquals('visitor.pass.used', $event->getEventName());
    }

    public function testEventVersion(): void
    {
        $event = new PassUsedEvent(1, 'TEST1234', 2, 3);

        $this->assertEquals('1.0', $event->getVersion());
    }

    public function testSerialization(): void
    {
        $passId = 999;
        $passCode = 'SERIAL01';
        $visitorId = 888;
        $operatorId = 777;

        $event = new PassUsedEvent($passId, $passCode, $visitorId, $operatorId);

        $serialized = serialize($event);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(PassUsedEvent::class, $unserialized);
        $this->assertEquals($event->getPassId(), $unserialized->getPassId());
        $this->assertEquals($event->getPassCode(), $unserialized->getPassCode());
        $this->assertEquals($event->getVisitorId(), $unserialized->getVisitorId());
        $this->assertEquals($event->getOperatorId(), $unserialized->getOperatorId());
        $this->assertEquals($event->getOccurredOn()->getTimestamp(), $unserialized->getOccurredOn()->getTimestamp());
    }

    public function testToArray(): void
    {
        $passId = 555;
        $passCode = 'ARRAY123';
        $visitorId = 666;
        $operatorId = 777;

        $event = new PassUsedEvent($passId, $passCode, $visitorId, $operatorId);
        $array = $event->toArray();

        $this->assertEquals($passId, $array['passId']);
        $this->assertEquals($passCode, $array['passCode']);
        $this->assertEquals($visitorId, $array['visitorId']);
        $this->assertEquals($operatorId, $array['operatorId']);
        $this->assertArrayHasKey('occurredOn', $array);
    }
}
