<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Entity\VisitorInvitation;
use Tourze\VisitorManageBundle\Enum\InvitationStatus;

class VisitorInvitationFixtures extends Fixture implements DependentFixtureInterface
{
    public const INVITATION_1_REFERENCE = 'invitation-1';

    public function load(ObjectManager $manager): void
    {
        // 使用引用获取访客数据
        /** @var Visitor $visitor1 */
        $visitor1 = $this->getReference(VisitorFixtures::VISITOR_1_REFERENCE, Visitor::class);

        // 创建已确认的邀请
        $invitation1 = new VisitorInvitation();
        $invitation1->setInviter(1001);
        $invitation1->setVisitor($visitor1);
        $invitation1->setInviteCode('INV2024001');
        $invitation1->setStatus(InvitationStatus::CONFIRMED);
        $invitation1->setExpireTime(new \DateTimeImmutable('+7 days'));
        $invitation1->setCreateTime(new \DateTimeImmutable());

        $manager->persist($invitation1);
        $this->addReference(self::INVITATION_1_REFERENCE, $invitation1);

        // 创建新的邀请记录
        $visitor = new Visitor();
        $visitor->setName('钱七');
        $visitor->setMobile('13500135000');
        $visitor->setCompany('合作伙伴');
        $visitor->setReason('合作洽谈');
        $visitor->setAppointmentTime(new \DateTimeImmutable('+1 day'));
        $visitor->setCreateTime(new \DateTimeImmutable());
        $visitor->setUpdateTime(new \DateTimeImmutable());

        $invitation2 = new VisitorInvitation();
        $invitation2->setInviter(1002);
        $invitation2->setVisitor($visitor);
        $invitation2->setInviteCode('INV2024002');
        $invitation2->setStatus(InvitationStatus::PENDING);
        $invitation2->setExpireTime(new \DateTimeImmutable('+3 days'));
        $invitation2->setCreateTime(new \DateTimeImmutable());

        // 创建已过期的邀请
        $expiredVisitor = new Visitor();
        $expiredVisitor->setName('孙八');
        $expiredVisitor->setMobile('13400134000');
        $expiredVisitor->setCompany('过期公司');
        $expiredVisitor->setReason('过期访问');
        $expiredVisitor->setAppointmentTime(new \DateTimeImmutable('-2 days'));
        $expiredVisitor->setCreateTime(new \DateTimeImmutable('-3 days'));
        $expiredVisitor->setUpdateTime(new \DateTimeImmutable('-3 days'));

        $invitation3 = new VisitorInvitation();
        $invitation3->setInviter(1003);
        $invitation3->setVisitor($expiredVisitor);
        $invitation3->setInviteCode('INV2024003');
        $invitation3->setStatus(InvitationStatus::EXPIRED);
        $invitation3->setExpireTime(new \DateTimeImmutable('-1 day'));
        $invitation3->setCreateTime(new \DateTimeImmutable('-3 days'));

        $manager->persist($visitor);
        $manager->persist($expiredVisitor);
        $manager->persist($invitation2);
        $manager->persist($invitation3);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            VisitorFixtures::class,
        ];
    }
}
