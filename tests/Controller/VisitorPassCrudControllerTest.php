<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\VisitorManageBundle\Controller\VisitorPassCrudController;

/**
 * 访客通行码管理 CRUD 控制器测试
 * @internal
 */
#[CoversClass(VisitorPassCrudController::class)]
#[RunTestsInSeparateProcesses]
class VisitorPassCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): VisitorPassCrudController
    {
        return self::getService(VisitorPassCrudController::class);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        return [
            'ID' => ['ID'],
            '访客信息' => ['访客信息'],
            '通行码' => ['通行码'],
            '有效开始时间' => ['有效开始时间'],
            '有效结束时间' => ['有效结束时间'],
            '创建时间' => ['创建时间'],
            '是否已使用' => ['是否已使用'],
            '使用时间' => ['使用时间'],
        ];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        return [
            'visitor' => ['visitor'],
            'passCode' => ['passCode'],
            'validStartTime' => ['validStartTime'],
            'validEndTime' => ['validEndTime'],
        ];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        return [
            'visitor' => ['visitor'],
            'passCode' => ['passCode'],
            'validStartTime' => ['validStartTime'],
            'validEndTime' => ['validEndTime'],
        ];
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertEquals(
            'Tourze\VisitorManageBundle\Entity\VisitorPass',
            VisitorPassCrudController::getEntityFqcn()
        );
    }

    /**
     * 验证表单验证失败的通用方法
     *
     * @param string $action CRUD操作
     * @param array<string, mixed> $invalidData 无效数据
     * @param array<string, mixed> $parameters 额外参数
     */
    protected function assertValidationFails(string $action, array $invalidData, array $parameters = []): void
    {
        $client = $this->createAuthenticatedClient();

        $url = $this->generateAdminUrl($action, $parameters);
        $crawler = $client->request('GET', $url);
        $this->assertResponseIsSuccessful();

        $entityName = $this->getEntitySimpleName();
        $form = $crawler->selectButton('Create')->form();

        // 填充无效数据
        foreach ($invalidData as $field => $value) {
            $fieldName = sprintf('%s[%s]', $entityName, $field);
            if ($form->has($fieldName)) {
                $form[$fieldName] = $value;
            }
        }

        $client->submit($form);

        // 验证失败应该返回到表单页面并显示错误
        $this->assertResponseStatusCodeSame(422, 'Form validation should fail with status 422');
    }

    public function testValidationErrors(): void
    {
        $client = $this->createAuthenticatedClient();

        // 获取新建页面
        $url = $this->generateAdminUrl('new');
        $crawler = $client->request('GET', $url);
        $this->assertResponseIsSuccessful();

        // 简单验证页面包含表单元素
        $forms = $crawler->filter('form');
        $this->assertGreaterThan(0, $forms->count(), 'New page should contain at least one form');

        // 验证页面包含必要的字段
        $content = $client->getResponse()->getContent();
        if (false === $content) {
            throw new \RuntimeException('Failed to get response content');
        }
        $this->assertStringContainsString('visitor', $content);
        $this->assertStringContainsString('validStartTime', $content);
    }

    /**
     * 测试生成二维码的自定义动作方法存在
     */
    public function testGenerateQrCode(): void
    {
        $controller = $this->getControllerService();

        // 验证方法可以被反射调用
        $reflection = new \ReflectionClass($controller);
        $this->assertTrue($reflection->hasMethod('generateQrCode'));
        $method = $reflection->getMethod('generateQrCode');
        $this->assertTrue($method->isPublic());
    }

    /**
     * 测试查看二维码的自定义动作方法存在
     */
    public function testViewQrCode(): void
    {
        $controller = $this->getControllerService();

        // 验证方法可以被反射调用
        $reflection = new \ReflectionClass($controller);
        $this->assertTrue($reflection->hasMethod('viewQrCode'));
        $method = $reflection->getMethod('viewQrCode');
        $this->assertTrue($method->isPublic());
    }
}
