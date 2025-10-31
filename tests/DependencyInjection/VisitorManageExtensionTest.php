<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use Tourze\VisitorManageBundle\DependencyInjection\VisitorManageExtension;

/**
 * @internal
 */
#[CoversClass(VisitorManageExtension::class)]
class VisitorManageExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    public function testPrepend(): void
    {
        $container = new ContainerBuilder();
        $extension = new VisitorManageExtension();

        // 确保初始时twig配置为空
        $initialConfig = $container->getExtensionConfig('twig');
        $this->assertEmpty($initialConfig);

        // 调用prepend方法
        $extension->prepend($container);

        // 验证twig扩展配置已被添加
        $twigConfig = $container->getExtensionConfig('twig');
        $this->assertIsArray($twigConfig);
        $this->assertNotEmpty($twigConfig);

        // 验证paths配置
        $this->assertArrayHasKey('paths', $twigConfig[0]);
        $this->assertIsArray($twigConfig[0]['paths']);
    }
}
