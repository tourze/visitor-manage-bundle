<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Entity\VisitorPass;

class VisitorPassFixtures extends Fixture implements DependentFixtureInterface
{
    public const PASS_1_REFERENCE = 'pass-1';

    public function load(ObjectManager $manager): void
    {
        // 使用引用获取访客数据
        /** @var Visitor $visitor2 */
        $visitor2 = $this->getReference(VisitorFixtures::VISITOR_2_REFERENCE, Visitor::class);

        // 为已签到的访客创建通行码
        $pass1 = new VisitorPass();
        $pass1->setVisitor($visitor2);
        $pass1->setPassCode('VP2024001');
        $pass1->setQrCode('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        $pass1->setValidStartTime(new \DateTimeImmutable('-1 hour'));
        $pass1->setValidEndTime(new \DateTimeImmutable('+8 hours'));
        $pass1->setCreateTime(new \DateTimeImmutable());

        $manager->persist($pass1);
        $this->addReference(self::PASS_1_REFERENCE, $pass1);

        // 创建新的访客和通行码
        $visitor = new Visitor();
        $visitor->setName('周九');
        $visitor->setMobile('13300133000');
        $visitor->setCompany('新访客公司');
        $visitor->setReason('系统测试');
        $visitor->setAppointmentTime(new \DateTimeImmutable('+2 hours'));
        $visitor->setCreateTime(new \DateTimeImmutable());
        $visitor->setUpdateTime(new \DateTimeImmutable());

        $pass2 = new VisitorPass();
        $pass2->setVisitor($visitor);
        $pass2->setPassCode('VP2024002');
        $pass2->setQrCode('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        $pass2->setValidStartTime(new \DateTimeImmutable('+30 minutes'));
        $pass2->setValidEndTime(new \DateTimeImmutable('+10 hours'));
        $pass2->setCreateTime(new \DateTimeImmutable());

        // 创建已使用的通行码
        $usedVisitor = new Visitor();
        $usedVisitor->setName('吴十');
        $usedVisitor->setMobile('13200132000');
        $usedVisitor->setCompany('已访问公司');
        $usedVisitor->setReason('已完成访问');
        $usedVisitor->setAppointmentTime(new \DateTimeImmutable('-3 hours'));
        $usedVisitor->setCreateTime(new \DateTimeImmutable('-4 hours'));
        $usedVisitor->setUpdateTime(new \DateTimeImmutable('-4 hours'));
        $usedVisitor->setSignInTime(new \DateTimeImmutable('-2 hours'));
        $usedVisitor->setSignOutTime(new \DateTimeImmutable('-1 hour'));

        $pass3 = new VisitorPass();
        $pass3->setVisitor($usedVisitor);
        $pass3->setPassCode('VP2024003');
        $pass3->setQrCode('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        $pass3->setValidStartTime(new \DateTimeImmutable('-3 hours'));
        $pass3->setValidEndTime(new \DateTimeImmutable('+5 hours'));
        $pass3->setUseTime(new \DateTimeImmutable('-1 hour'));
        $pass3->setCreateTime(new \DateTimeImmutable('-4 hours'));

        $manager->persist($visitor);
        $manager->persist($usedVisitor);
        $manager->persist($pass2);
        $manager->persist($pass3);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            VisitorFixtures::class,
        ];
    }
}
