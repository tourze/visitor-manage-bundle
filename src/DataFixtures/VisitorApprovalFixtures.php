<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Entity\VisitorApproval;
use Tourze\VisitorManageBundle\Enum\ApprovalStatus;
use Tourze\VisitorManageBundle\Enum\VisitorStatus;

class VisitorApprovalFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 创建一个已批准的访客和审批记录
        $visitor1 = new Visitor();
        $visitor1->setName('李四');
        $visitor1->setMobile('13900139000');
        $visitor1->setCompany('另一家公司');
        $visitor1->setReason('技术交流');
        $visitor1->setVehicleNumber('京A12345');
        $visitor1->setContactPerson('王经理');
        $visitor1->setAppointmentTime(new \DateTimeImmutable('+2 days'));
        $visitor1->setStatus(VisitorStatus::APPROVED);
        $visitor1->setSignInTime(new \DateTimeImmutable('-15 minutes'));

        $approval1 = new VisitorApproval();
        $approval1->setVisitor($visitor1);
        $approval1->setStatus(ApprovalStatus::APPROVED);
        $approval1->setApproveTime(new \DateTimeImmutable('-30 minutes'));

        // 创建一个已完成访问的访客和审批记录
        $visitor2 = new Visitor();
        $visitor2->setName('王五');
        $visitor2->setMobile('13700137000');
        $visitor2->setCompany('供应商');
        $visitor2->setReason('送货');
        $visitor2->setVehicleNumber('京B67890');
        $visitor2->setAppointmentTime(new \DateTimeImmutable('-1 day'));
        $visitor2->setStatus(VisitorStatus::SIGNED_OUT);
        $visitor2->setSignInTime(new \DateTimeImmutable('-1 day'));
        $visitor2->setSignOutTime(new \DateTimeImmutable('-23 hours'));

        $approval2 = new VisitorApproval();
        $approval2->setVisitor($visitor2);
        $approval2->setStatus(ApprovalStatus::APPROVED);
        $approval2->setApproveTime(new \DateTimeImmutable('-1 day'));

        // 创建一个待审批的访客和审批记录
        $visitor3 = new Visitor();
        $visitor3->setName('赵六');
        $visitor3->setMobile('13600136000');
        $visitor3->setCompany('新公司');
        $visitor3->setReason('面试');
        $visitor3->setAppointmentTime(new \DateTimeImmutable('+3 hours'));
        $visitor3->setStatus(VisitorStatus::PENDING);

        $approval3 = new VisitorApproval();
        $approval3->setVisitor($visitor3);
        $approval3->setStatus(ApprovalStatus::PENDING);

        // 创建一个被拒绝的访客和审批记录
        $visitor4 = new Visitor();
        $visitor4->setName('钱七');
        $visitor4->setMobile('13500135000');
        $visitor4->setCompany('拒绝公司');
        $visitor4->setReason('不符合要求');
        $visitor4->setAppointmentTime(new \DateTimeImmutable('+1 hour'));
        $visitor4->setStatus(VisitorStatus::REJECTED);

        $approval4 = new VisitorApproval();
        $approval4->setVisitor($visitor4);
        $approval4->setStatus(ApprovalStatus::REJECTED);
        $approval4->setRejectReason('访客信息不完整');
        $approval4->setApproveTime(new \DateTimeImmutable('-1 hour'));

        $manager->persist($visitor1);
        $manager->persist($visitor2);
        $manager->persist($visitor3);
        $manager->persist($visitor4);
        $manager->persist($approval1);
        $manager->persist($approval2);
        $manager->persist($approval3);
        $manager->persist($approval4);

        $manager->flush();
    }
}
