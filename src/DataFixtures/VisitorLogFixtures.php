<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Entity\VisitorLog;
use Tourze\VisitorManageBundle\Enum\VisitorAction;

class VisitorLogFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // 使用引用获取访客数据
        /** @var Visitor $visitor1 */
        $visitor1 = $this->getReference(VisitorFixtures::VISITOR_1_REFERENCE, Visitor::class);
        /** @var Visitor $visitor2 */
        $visitor2 = $this->getReference(VisitorFixtures::VISITOR_2_REFERENCE, Visitor::class);
        /** @var Visitor $visitor3 */
        $visitor3 = $this->getReference(VisitorFixtures::VISITOR_3_REFERENCE, Visitor::class);

        // 为访客1创建日志
        $log1 = new VisitorLog();
        $log1->setVisitor($visitor1);
        $log1->setAction(VisitorAction::REGISTERED);
        $log1->setOperator(1001);
        $log1->setRemark('访客注册');
        $log1->setCreateTime(new \DateTimeImmutable());

        // 为访客2创建日志
        $log2 = new VisitorLog();
        $log2->setVisitor($visitor2);
        $log2->setAction(VisitorAction::REGISTERED);
        $log2->setOperator(1002);
        $log2->setRemark('访客注册');
        $log2->setCreateTime(new \DateTimeImmutable());

        $log3 = new VisitorLog();
        $log3->setVisitor($visitor2);
        $log3->setAction(VisitorAction::APPROVED);
        $log3->setOperator(2001);
        $log3->setRemark('管理员审批通过');
        $log3->setCreateTime(new \DateTimeImmutable());

        $log4 = new VisitorLog();
        $log4->setVisitor($visitor2);
        $log4->setAction(VisitorAction::SIGNED_IN);
        $log4->setOperator(1002);
        $log4->setRemark('访客签到');
        $log4->setCreateTime(new \DateTimeImmutable());

        $manager->persist($log1);
        $manager->persist($log2);
        $manager->persist($log3);
        $manager->persist($log4);

        // 为访客3创建日志
        $log5 = new VisitorLog();
        $log5->setVisitor($visitor3);
        $log5->setAction(VisitorAction::REGISTERED);
        $log5->setOperator(1003);
        $log5->setRemark('访客注册');
        $log5->setCreateTime(new \DateTimeImmutable());

        $log6 = new VisitorLog();
        $log6->setVisitor($visitor3);
        $log6->setAction(VisitorAction::APPROVED);
        $log6->setOperator(2001);
        $log6->setRemark('管理员审批通过');
        $log6->setCreateTime(new \DateTimeImmutable());

        $log7 = new VisitorLog();
        $log7->setVisitor($visitor3);
        $log7->setAction(VisitorAction::SIGNED_IN);
        $log7->setOperator(1003);
        $log7->setRemark('访客签到');
        $log7->setCreateTime(new \DateTimeImmutable());

        $log8 = new VisitorLog();
        $log8->setVisitor($visitor3);
        $log8->setAction(VisitorAction::SIGNED_OUT);
        $log8->setOperator(1003);
        $log8->setRemark('访客签退');
        $log8->setCreateTime(new \DateTimeImmutable());

        $manager->persist($log5);
        $manager->persist($log6);
        $manager->persist($log7);
        $manager->persist($log8);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            VisitorFixtures::class,
        ];
    }
}
