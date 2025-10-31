<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Enum\VisitorStatus;

class VisitorFixtures extends Fixture
{
    public const VISITOR_1_REFERENCE = 'visitor-1';
    public const VISITOR_2_REFERENCE = 'visitor-2';
    public const VISITOR_3_REFERENCE = 'visitor-3';

    public function load(ObjectManager $manager): void
    {
        // 创建测试访客数据
        $visitor1 = new Visitor();
        $visitor1->setName('张三');
        $visitor1->setMobile('13800138000');
        $visitor1->setCompany('测试公司');
        $visitor1->setReason('业务洽谈');
        $visitor1->setAppointmentTime(new \DateTimeImmutable('+1 day'));
        $visitor1->setStatus(VisitorStatus::PENDING);
        $visitor1->setCreateTime(new \DateTimeImmutable());
        $visitor1->setUpdateTime(new \DateTimeImmutable());

        $visitor2 = new Visitor();
        $visitor2->setName('李四');
        $visitor2->setMobile('13900139000');
        $visitor2->setCompany('另一家公司');
        $visitor2->setReason('技术交流');
        $visitor2->setVehicleNumber('京A12345');
        $visitor2->setContactPerson('王经理');
        $visitor2->setAppointmentTime(new \DateTimeImmutable('+2 days'));
        $visitor2->setStatus(VisitorStatus::APPROVED);
        $visitor2->setCreateTime(new \DateTimeImmutable('-1 hour'));
        $visitor2->setUpdateTime(new \DateTimeImmutable('-30 minutes'));
        $visitor2->setSignInTime(new \DateTimeImmutable('-15 minutes'));

        $visitor3 = new Visitor();
        $visitor3->setName('王五');
        $visitor3->setMobile('13700137000');
        $visitor3->setCompany('供应商');
        $visitor3->setReason('送货');
        $visitor3->setVehicleNumber('京B67890');
        $visitor3->setAppointmentTime(new \DateTimeImmutable('-1 day'));
        $visitor3->setStatus(VisitorStatus::SIGNED_OUT);
        $visitor3->setCreateTime(new \DateTimeImmutable('-2 days'));
        $visitor3->setUpdateTime(new \DateTimeImmutable('-1 day'));
        $visitor3->setSignInTime(new \DateTimeImmutable('-1 day'));
        $visitor3->setSignOutTime(new \DateTimeImmutable('-23 hours'));

        $manager->persist($visitor1);
        $manager->persist($visitor2);
        $manager->persist($visitor3);

        // 添加引用
        $this->addReference(self::VISITOR_1_REFERENCE, $visitor1);
        $this->addReference(self::VISITOR_2_REFERENCE, $visitor2);
        $this->addReference(self::VISITOR_3_REFERENCE, $visitor3);

        $manager->flush();
    }
}
