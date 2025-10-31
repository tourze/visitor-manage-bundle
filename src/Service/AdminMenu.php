<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Service;

use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Entity\VisitorApproval;
use Tourze\VisitorManageBundle\Entity\VisitorInvitation;
use Tourze\VisitorManageBundle\Entity\VisitorLog;
use Tourze\VisitorManageBundle\Entity\VisitorPass;

/**
 * 访客管理后台菜单提供者
 */
#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        // 添加访客管理主菜单
        if (null === $item->getChild('访客管理')) {
            $item->addChild('访客管理')
                ->setAttribute('icon', 'fas fa-users')
            ;
        }

        $visitorMenu = $item->getChild('访客管理');
        if (null === $visitorMenu) {
            return;
        }

        // 添加访客信息管理
        $visitorMenu->addChild('访客信息')
            ->setUri($this->linkGenerator->getCurdListPage(Visitor::class))
            ->setAttribute('icon', 'fas fa-user')
        ;

        // 添加访客审批管理
        $visitorMenu->addChild('访客审批')
            ->setUri($this->linkGenerator->getCurdListPage(VisitorApproval::class))
            ->setAttribute('icon', 'fas fa-check-circle')
        ;

        // 添加访客邀请管理
        $visitorMenu->addChild('访客邀请')
            ->setUri($this->linkGenerator->getCurdListPage(VisitorInvitation::class))
            ->setAttribute('icon', 'fas fa-envelope')
        ;

        // 添加访客通行码管理
        $visitorMenu->addChild('访客通行码')
            ->setUri($this->linkGenerator->getCurdListPage(VisitorPass::class))
            ->setAttribute('icon', 'fas fa-qrcode')
        ;

        // 添加访客日志管理
        $visitorMenu->addChild('访客日志')
            ->setUri($this->linkGenerator->getCurdListPage(VisitorLog::class))
            ->setAttribute('icon', 'fas fa-history')
        ;
    }
}
