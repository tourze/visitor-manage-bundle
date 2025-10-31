<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\VisitorManageBundle\Controller\VisitorApprovalCrudController;

/**
 * 访客审批管理 CRUD 控制器测试
 * @internal
 */
#[CoversClass(VisitorApprovalCrudController::class)]
#[RunTestsInSeparateProcesses]
class VisitorApprovalCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): VisitorApprovalCrudController
    {
        return self::getService(VisitorApprovalCrudController::class);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        return [
            'ID' => ['ID'],
            '访客信息' => ['访客信息'],
            '审批状态' => ['审批状态'],
            '审批时间' => ['审批时间'],
            '创建时间' => ['创建时间'],
        ];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        return [
            'visitor' => ['visitor'],
            'status' => ['status'],
            'rejectReason' => ['rejectReason'],
        ];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        return [
            'visitor' => ['visitor'],
            'status' => ['status'],
            'rejectReason' => ['rejectReason'],
        ];
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertEquals(
            'Tourze\VisitorManageBundle\Entity\VisitorApproval',
            VisitorApprovalCrudController::getEntityFqcn()
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

        // 查找提交按钮并获取表单
        $form = $crawler->selectButton('Create')->form();

        // 提交空表单（不填写任何必填字段）
        $client->submit($form);

        // EasyAdmin在必填关联字段为空时可能重定向回表单页面（302）或返回验证错误（422）
        // 我们验证响应不是成功创建（不是200或201），且要么是重定向要么是验证错误
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertTrue(
            302 === $statusCode || 422 === $statusCode,
            sprintf('Empty form submission should return 302 (redirect) or 422 (validation error), got %d', $statusCode)
        );

        // 如果是重定向，验证重定向回新建页面
        if (302 === $statusCode) {
            $this->assertResponseRedirects();
            $crawler = $client->followRedirect();
            // 验证回到了新建页面
            $this->assertStringContainsString('/new', $client->getRequest()->getRequestUri());
        } else {
            // 如果是422，验证包含错误信息
            $content = $client->getResponse()->getContent();
            if (false === $content) {
                throw new \RuntimeException('Failed to get response content');
            }
            $hasValidationError = str_contains($content, 'invalid-feedback')
                || str_contains($content, 'error')
                || str_contains($content, 'required')
                || str_contains($content, 'should not be blank')
                || str_contains($content, 'must be');
            $this->assertTrue($hasValidationError, 'Response should contain validation error messages');
        }
    }
}
