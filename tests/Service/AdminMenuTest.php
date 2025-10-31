<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;
use Tourze\VisitorManageBundle\Service\AdminMenu;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    private AdminMenu $adminMenu;

    private LinkGeneratorInterface $linkGenerator;

    protected function onSetUp(): void
    {
        $this->linkGenerator = new class implements LinkGeneratorInterface {
            private ?string $dashboardControllerFqcn = null;

            public function getCurdListPage(string $entityClass): string
            {
                return '/admin/mock-url';
            }

            public function extractEntityFqcn(string $url): ?string
            {
                return null;
            }

            public function setDashboard(string $dashboardControllerFqcn): void
            {
                $this->dashboardControllerFqcn = $dashboardControllerFqcn;
            }

            public function getDashboard(): ?string
            {
                return $this->dashboardControllerFqcn;
            }
        };

        // 替换容器中的LinkGenerator服务为我们的Mock
        self::getContainer()->set(LinkGeneratorInterface::class, $this->linkGenerator);
        $this->adminMenu = self::getService(AdminMenu::class);
    }

    public function testImplementsMenuProviderInterface(): void
    {
        // AdminMenu类型已由构造函数确定
        // 此测试验证服务可以正常从容器获取，并且可调用
        $reflection = new \ReflectionClass($this->adminMenu);
        self::assertTrue($reflection->hasMethod('__invoke'));
    }

    public function testIsCallable(): void
    {
        $this->assertIsCallable($this->adminMenu, 'AdminMenu service should be callable');
    }

    public function testInvokeAddsVisitorManagementMenu(): void
    {
        $factory = new MenuFactory();
        $rootMenu = new MenuItem('root', $factory);

        ($this->adminMenu)($rootMenu);

        $visitorMenu = $rootMenu->getChild('访客管理');
        self::assertNotNull($visitorMenu);
        self::assertSame('访客管理', $visitorMenu->getName());
        self::assertSame('fas fa-users', $visitorMenu->getAttribute('icon'));
    }

    public function testInvokeAddsAllSubMenuItems(): void
    {
        $factory = new MenuFactory();
        $rootMenu = new MenuItem('root', $factory);

        ($this->adminMenu)($rootMenu);

        $visitorMenu = $rootMenu->getChild('访客管理');
        self::assertNotNull($visitorMenu);

        // 检查所有子菜单项
        $expectedSubMenus = [
            '访客信息' => 'fas fa-user',
            '访客审批' => 'fas fa-check-circle',
            '访客邀请' => 'fas fa-envelope',
            '访客通行码' => 'fas fa-qrcode',
            '访客日志' => 'fas fa-history',
        ];

        foreach ($expectedSubMenus as $name => $icon) {
            $subMenu = $visitorMenu->getChild($name);
            self::assertNotNull($subMenu);
            self::assertSame($name, $subMenu->getName());
            self::assertSame($icon, $subMenu->getAttribute('icon'));
            self::assertSame('/admin/mock-url', $subMenu->getUri());
        }
    }

    public function testInvokeCallsLinkGeneratorForAllEntities(): void
    {
        // 使用容器中的AdminMenu服务来测试
        $factory = new MenuFactory();
        $rootMenu = new MenuItem('root', $factory);

        ($this->adminMenu)($rootMenu);

        $visitorMenu = $rootMenu->getChild('访客管理');
        self::assertNotNull($visitorMenu);

        // 验证所有预期的子菜单都被创建，这间接验证了LinkGenerator被正确调用
        $expectedSubMenus = ['访客信息', '访客审批', '访客邀请', '访客通行码', '访客日志'];

        foreach ($expectedSubMenus as $menuName) {
            $subMenu = $visitorMenu->getChild($menuName);
            self::assertNotNull($subMenu, "子菜单 '{$menuName}' 应该存在");
            self::assertNotEmpty($subMenu->getUri(), "子菜单 '{$menuName}' 应该有有效的URI");
        }

        // 验证菜单数量
        self::assertCount(5, $visitorMenu->getChildren(), '应该创建5个子菜单项');
    }

    public function testInvokeWithExistingVisitorMenu(): void
    {
        $factory = new MenuFactory();
        $rootMenu = new MenuItem('root', $factory);
        $existingVisitorMenu = $rootMenu->addChild('访客管理');

        ($this->adminMenu)($rootMenu);

        // 应该使用现有的菜单，而不是创建新的
        self::assertSame($existingVisitorMenu, $rootMenu->getChild('访客管理'));

        // 但应该添加所有子菜单项
        $visitorMenu = $rootMenu->getChild('访客管理');
        self::assertCount(5, $visitorMenu->getChildren());
    }

    public function testInvokeHandlesNullVisitorMenu(): void
    {
        // 使用 PHPUnit mock 简化测试
        $rootMenu = $this->createMock(ItemInterface::class);

        // 配置 getChild 被调用两次，第一次返回 null，第二次返回实际菜单
        $factory = new MenuFactory();
        $newMenu = new MenuItem('访客管理', $factory);

        $rootMenu->expects($this->exactly(2))
            ->method('getChild')
            ->with('访客管理')
            ->willReturnOnConsecutiveCalls(null, $newMenu)
        ;

        // 验证会调用 addChild 来创建新菜单
        $rootMenu->expects($this->once())
            ->method('addChild')
            ->with('访客管理')
            ->willReturn($newMenu)
        ;

        ($this->adminMenu)($rootMenu);
    }
}
