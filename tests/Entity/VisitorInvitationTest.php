<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\BizUserBundle\Entity\BizUser;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Entity\VisitorInvitation;
use Tourze\VisitorManageBundle\Enum\InvitationStatus;

/**
 * @internal
 */
#[CoversClass(VisitorInvitation::class)]
class VisitorInvitationTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new VisitorInvitation();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'inviteCode' => ['inviteCode', 'test_code'],
            'status' => ['status', InvitationStatus::PENDING],
        ];
    }

    public function testInvitationCreation(): void
    {
        $invitation = new VisitorInvitation();

        $this->assertInstanceOf(VisitorInvitation::class, $invitation);
        $this->assertNull($invitation->getId());
        $this->assertEquals(InvitationStatus::PENDING, $invitation->getStatus());
    }

    public function testCustomGettersAndSetters(): void
    {
        $invitation = new VisitorInvitation();
        $visitor = new Visitor();
        $expireTime = new \DateTimeImmutable('+1 week');
        $createTime = new \DateTimeImmutable();

        // Test invite code
        $invitation->setInviteCode('INV123456');
        $this->assertEquals('INV123456', $invitation->getInviteCode());

        // Test visitor relation
        $invitation->setVisitor($visitor);
        $this->assertSame($visitor, $invitation->getVisitor());

        // Test status
        $invitation->setStatus(InvitationStatus::CONFIRMED);
        $this->assertEquals(InvitationStatus::CONFIRMED, $invitation->getStatus());

        // Test expire at
        $invitation->setExpireTime($expireTime);
        $this->assertEquals($expireTime, $invitation->getExpireTime());

        // Test created at
        $invitation->setCreateTime($createTime);
        $this->assertEquals($createTime, $invitation->getCreateTime());
    }

    public function testInviterRelation(): void
    {
        $invitation = new VisitorInvitation();

        // Test setting null inviter
        $invitation->setInviter(null);
        $this->assertNull($invitation->getInviter());

        // Note: Actual BizUser testing will be done when integration is available
    }

    public function testVisitorRelation(): void
    {
        $invitation = new VisitorInvitation();
        $visitor = new Visitor();

        $invitation->setVisitor($visitor);
        $this->assertSame($visitor, $invitation->getVisitor());
    }

    public function testInviteCodeGeneration(): void
    {
        $invitation = new VisitorInvitation();

        // Test that invite code can be set
        $invitation->setInviteCode('INVITE-12345-ABCDE');
        $this->assertEquals('INVITE-12345-ABCDE', $invitation->getInviteCode());

        // Test uniqueness will be handled by service layer
    }

    public function testStatusTransitions(): void
    {
        $invitation = new VisitorInvitation();

        // Test all possible status values
        $invitation->setStatus(InvitationStatus::PENDING);
        $this->assertEquals(InvitationStatus::PENDING, $invitation->getStatus());

        $invitation->setStatus(InvitationStatus::CONFIRMED);
        $this->assertEquals(InvitationStatus::CONFIRMED, $invitation->getStatus());

        $invitation->setStatus(InvitationStatus::REJECTED);
        $this->assertEquals(InvitationStatus::REJECTED, $invitation->getStatus());

        $invitation->setStatus(InvitationStatus::EXPIRED);
        $this->assertEquals(InvitationStatus::EXPIRED, $invitation->getStatus());
    }

    public function testExpiration(): void
    {
        $invitation = new VisitorInvitation();
        $futureDate = new \DateTimeImmutable('+7 days');
        $pastDate = new \DateTimeImmutable('-1 day');

        // Test future expiration
        $invitation->setExpireTime($futureDate);
        $this->assertEquals($futureDate, $invitation->getExpireTime());

        // Test past expiration
        $invitation->setExpireTime($pastDate);
        $this->assertEquals($pastDate, $invitation->getExpireTime());

        // Business logic for checking expiration will be in service layer
    }
}
