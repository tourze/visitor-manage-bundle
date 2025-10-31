<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\VisitorManageBundle\Event\VisitorApprovedEvent;

/**
 * @internal
 */
#[CoversClass(VisitorApprovedEvent::class)]
class VisitorApprovedEventTest extends TestCase
{
    public function testApprovalEventData(): void
    {
        $visitorId = 555;
        $approverId = 777;
        $approverName = '审批人';
        $remark = '符合要求，批准进入';

        $event = new VisitorApprovedEvent($visitorId, $approverId, $approverName, $remark);

        $this->assertEquals($visitorId, $event->getVisitorId());
        $this->assertEquals($approverId, $event->getApproverId());
        $this->assertEquals($approverName, $event->getApproverName());
        $this->assertEquals($remark, $event->getRemark());
        $this->assertInstanceOf(\DateTimeImmutable::class, $event->getOccurredOn());
    }

    public function testApproverInformation(): void
    {
        $visitorId = 666;
        $approverId = 888;
        $approverName = '主管经理';
        $remark = '业务需要，允许访问';

        $event = new VisitorApprovedEvent($visitorId, $approverId, $approverName, $remark);

        $this->assertEquals('主管经理', $event->getApproverName());
        $this->assertEquals('业务需要，允许访问', $event->getRemark());
    }

    public function testEventName(): void
    {
        $event = new VisitorApprovedEvent(1, 1, 'Test', '');

        $this->assertEquals('visitor.approved', $event->getEventName());
    }

    public function testEventVersion(): void
    {
        $event = new VisitorApprovedEvent(1, 1, 'Test', '');

        $this->assertEquals('1.0', $event->getVersion());
    }

    public function testSerialization(): void
    {
        $visitorId = 999;
        $approverId = 1111;
        $approverName = '测试审批人';
        $remark = '测试备注';

        $event = new VisitorApprovedEvent($visitorId, $approverId, $approverName, $remark);

        $serialized = serialize($event);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(VisitorApprovedEvent::class, $unserialized);
        $this->assertEquals($event->getVisitorId(), $unserialized->getVisitorId());
        $this->assertEquals($event->getApproverId(), $unserialized->getApproverId());
        $this->assertEquals($event->getApproverName(), $unserialized->getApproverName());
        $this->assertEquals($event->getRemark(), $unserialized->getRemark());
    }

    public function testToArray(): void
    {
        $visitorId = 222;
        $approverId = 333;
        $approverName = '数组测试';
        $remark = '转换测试';

        $event = new VisitorApprovedEvent($visitorId, $approverId, $approverName, $remark);
        $array = $event->toArray();

        $this->assertEquals($visitorId, $array['visitorId']);
        $this->assertEquals($approverId, $array['approverId']);
        $this->assertEquals($approverName, $array['approverName']);
        $this->assertEquals($remark, $array['remark']);
        $this->assertArrayHasKey('occurredOn', $array);
    }
}
