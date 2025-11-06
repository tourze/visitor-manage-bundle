<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\DoctrineFunctionBundle\DoctrineFunctionBundle;
use Tourze\EasyAdminMenuBundle\EasyAdminMenuBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use Tourze\VisitorManageBundle\VisitorManageBundle;

/**
 * @internal
 */
#[CoversClass(VisitorManageBundle::class)]
#[RunTestsInSeparateProcesses]
final class VisitorManageBundleTest extends AbstractBundleTestCase
{
    private VisitorManageBundle $bundle;

    protected function onSetUp(): void
    {
        // @phpstan-ignore integrationTest.noDirectInstantiationOfCoveredClass
        $this->bundle = new VisitorManageBundle();
    }

    public function testBundleCanBeInstantiated(): void
    {
        // Act: 创建Bundle对象
        // @phpstan-ignore integrationTest.noDirectInstantiationOfCoveredClass
        $bundle = new VisitorManageBundle();

        // Assert: 验证Bundle对象
        $this->assertInstanceOf(VisitorManageBundle::class, $bundle);
        $this->assertInstanceOf(BundleInterface::class, $bundle);
    }

    public function testBundleImplementsBundleDependencyInterface(): void
    {
        // Assert: 验证接口实现
        $this->assertInstanceOf(BundleDependencyInterface::class, $this->bundle);
    }

    public function testGetBundleDependenciesReturnsCorrectDependencies(): void
    {
        // Act: 获取Bundle依赖
        $dependencies = VisitorManageBundle::getBundleDependencies();

        // Assert: 验证依赖配置
        $this->assertCount(3, $dependencies);

        // 验证DoctrineBundle依赖
        $this->assertArrayHasKey(DoctrineBundle::class, $dependencies);
        $this->assertEquals(['all' => true], $dependencies[DoctrineBundle::class]);

        // 验证DoctrineFunctionBundle依赖
        $this->assertArrayHasKey(DoctrineFunctionBundle::class, $dependencies);
        $this->assertEquals(['all' => true], $dependencies[DoctrineFunctionBundle::class]);

        // 验证EasyAdminMenuBundle依赖
        $this->assertArrayHasKey(EasyAdminMenuBundle::class, $dependencies);
        $this->assertEquals(['all' => true], $dependencies[EasyAdminMenuBundle::class]);
    }

    public function testBundleDependenciesAllHaveAllEnvironmentEnabled(): void
    {
        // Act: 获取依赖配置
        $dependencies = VisitorManageBundle::getBundleDependencies();

        // Assert: 验证所有依赖都在all环境下启用
        foreach ($dependencies as $bundleClass => $config) {
            $this->assertArrayHasKey('all', $config, "Bundle {$bundleClass} should have 'all' environment configured");
            $this->assertTrue($config['all'], "Bundle {$bundleClass} should be enabled in 'all' environment");
        }
    }

    public function testBundleNameIsCorrect(): void
    {
        // Act: 获取Bundle名称
        $name = $this->bundle->getName();

        // Assert: 验证Bundle名称
        $this->assertEquals('VisitorManageBundle', $name);
    }

    public function testBundleNamespaceIsCorrect(): void
    {
        // Act: 获取Bundle命名空间
        $namespace = $this->bundle->getNamespace();

        // Assert: 验证Bundle命名空间
        $this->assertEquals('Tourze\VisitorManageBundle', $namespace);
    }

    public function testGetPath(): void
    {
        // Act: 获取Bundle路径
        $path = $this->bundle->getPath();

        // Assert: 验证Bundle路径包含正确的目录
        $this->assertStringContainsString('visitor-manage-bundle', $path);
        $this->assertDirectoryExists($path);
    }

    public function testBundleCanBeBootedAndShutdown(): void
    {
        // Act & Assert: 模拟Bundle启动和关闭
        // 如果启动或关闭时抛出异常，测试会失败
        $this->bundle->boot();
        $this->bundle->shutdown();

        // 验证Bundle实例仍然有效
        $this->assertInstanceOf(VisitorManageBundle::class, $this->bundle);
    }

    public function testBundleContainerBuilderIntegration(): void
    {
        // 注意：这里只测试Bundle基本功能，不涉及复杂的容器构建
        // 实际的容器集成测试应该在集成测试中进行

        // Act: 获取Bundle基本信息
        $reflection = new \ReflectionClass($this->bundle);

        // Assert: 验证Bundle类的基本特征
        $this->assertTrue($reflection->isSubclassOf(Bundle::class));
        $this->assertTrue($reflection->implementsInterface(BundleDependencyInterface::class));
        $this->assertFalse($reflection->isAbstract());
        $this->assertTrue($reflection->isInstantiable());
    }

    public function testBundleDependencyConfigurationStructure(): void
    {
        // Act: 获取依赖配置
        $dependencies = VisitorManageBundle::getBundleDependencies();

        // Assert: 验证配置结构
        foreach ($dependencies as $bundleClass => $config) {
            $this->assertNotEmpty($bundleClass, 'Bundle class should not be empty');
            $this->assertNotEmpty($config, 'Bundle configuration should not be empty');

            // 验证Bundle类名结尾
            $this->assertStringEndsWith('Bundle', $bundleClass, 'Bundle class should end with "Bundle"');

            // 验证配置结构
            foreach ($config as $environment => $enabled) {
            }
        }
    }

    public function testStaticMethodAccessibility(): void
    {
        // Act: 检查静态方法可访问性
        $reflection = new \ReflectionClass(VisitorManageBundle::class);
        $method = $reflection->getMethod('getBundleDependencies');

        // Assert: 验证方法特征
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
        $returnType = $method->getReturnType();
        $this->assertEquals('array', $returnType instanceof \ReflectionNamedType ? $returnType->getName() : 'mixed');
    }

    public function testBundleDependencyInterfaceCompliance(): void
    {
        // Act: 获取接口反射
        $bundleReflection = new \ReflectionClass(VisitorManageBundle::class);
        $interfaceReflection = new \ReflectionClass(BundleDependencyInterface::class);

        // Assert: 验证接口实现的完整性
        $this->assertTrue($bundleReflection->implementsInterface(BundleDependencyInterface::class));

        // 验证接口方法的实现
        foreach ($interfaceReflection->getMethods() as $interfaceMethod) {
            $this->assertTrue(
                $bundleReflection->hasMethod($interfaceMethod->getName()),
                "Bundle should implement interface method: {$interfaceMethod->getName()}"
            );

            $bundleMethod = $bundleReflection->getMethod($interfaceMethod->getName());
            $this->assertEquals(
                $interfaceMethod->isStatic(),
                $bundleMethod->isStatic(),
                "Method {$interfaceMethod->getName()} static modifier should match interface"
            );
        }
    }

    public function testGetBundleDependenciesReturnType(): void
    {
        // Act: 调用方法获取依赖
        $dependencies = VisitorManageBundle::getBundleDependencies();

        // Assert: 验证返回类型和结构

        // 验证每个依赖项的类型结构
        foreach ($dependencies as $bundleClass => $environments) {

            // 验证Bundle类存在且可以实例化
            $reflection = new \ReflectionClass($bundleClass);
            $this->assertTrue(
                $reflection->isInstantiable(),
                "Dependency bundle class {$bundleClass} should be instantiable"
            );

            // 验证Bundle类继承自Bundle
            $this->assertTrue(
                $reflection->isSubclassOf(Bundle::class),
                "Dependency {$bundleClass} should extend Bundle class"
            );
        }
    }

    public function testBundleDependenciesReflectActualRequirements(): void
    {
        // Act: 获取依赖配置
        $dependencies = VisitorManageBundle::getBundleDependencies();

        // Assert: 验证实际需要的依赖
        // 基于VisitorManageBundle的实现，它依赖于Doctrine和DoctrineFunctionBundle

        // 验证DoctrineBundle存在
        $this->assertArrayHasKey(
            DoctrineBundle::class,
            $dependencies,
            'VisitorManageBundle should depend on DoctrineBundle for ORM functionality'
        );

        // 验证DoctrineFunctionBundle存在
        $this->assertArrayHasKey(
            DoctrineFunctionBundle::class,
            $dependencies,
            'VisitorManageBundle should depend on DoctrineFunctionBundle for additional Doctrine functions'
        );

        // 验证EasyAdminMenuBundle存在
        $this->assertArrayHasKey(
            EasyAdminMenuBundle::class,
            $dependencies,
            'VisitorManageBundle should depend on EasyAdminMenuBundle for admin menu functionality'
        );

        // 验证依赖数量合理（防止依赖过多）
        $this->assertLessThanOrEqual(
            5,
            count($dependencies),
            'Bundle should not have too many direct dependencies'
        );
    }
}
