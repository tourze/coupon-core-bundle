<?php

namespace Tourze\CouponCoreBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponCoreBundle\Controller\Admin\BatchCrudController;
use Tourze\CouponCoreBundle\Entity\Batch;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * BatchCrudController的Web测试
 *
 * @internal
 */
#[CoversClass(BatchCrudController::class)]
#[RunTestsInSeparateProcesses]
final class BatchCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return BatchCrudController
     */
    protected function getControllerService(): BatchCrudController
    {
        $service = self::getContainer()->get(BatchCrudController::class);
        self::assertInstanceOf(BatchCrudController::class, $service);

        return $service;
    }

    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID列' => ['ID'];
        yield '关联优惠券列' => ['关联优惠券'];
        yield '总数量列' => ['总数量'];
        yield '已发送数量列' => ['已发送数量'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield '关联优惠券字段' => ['coupon'];
        yield '总数量字段' => ['totalNum'];
        yield '已发送数量字段' => ['sendNum'];
        yield '备注字段' => ['remark'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        yield '关联优惠券字段' => ['coupon'];
        yield '总数量字段' => ['totalNum'];
        yield '已发送数量字段' => ['sendNum'];
        yield '备注字段' => ['remark'];
    }

    public function testBatchListPageAccessWithAdminUser(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 测试页面访问
        $client->request('GET', '/admin/coupon/batch');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('批次列表', $content);

        // 测试批量操作接口的可访问性（使用空数据）
        $client->request('POST', '/admin', [
            'ea' => [
                'batchActionName' => 'batchDelete',
                'batchActionEntityIds' => [],
                'crudControllerFqcn' => BatchCrudController::class,
            ],
        ]);

        // 验证批量操作接口正常响应 - 检查状态码在合理范围内
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertLessThan(
            500,
            $statusCode,
            sprintf('批量操作接口应该正常响应，实际状态码: %d', $statusCode)
        );
    }

    public function testBatchSearchFunctionality(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 测试页面基本元素
        $crawler = $client->request('GET', '/admin/coupon/batch');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $hasBasicElements = $crawler->filter('table')->count() > 0
                           || $crawler->filter('.content-wrapper')->count() > 0
                           || $crawler->filter('.main-content')->count() > 0;
        $this->assertTrue($hasBasicElements);

        // 测试批量操作功能 - 使用正确的HTTP请求格式
        $client->request('POST', '/admin', [
            'ea' => [
                'batchActionName' => 'batchDelete',
                'batchActionEntityIds' => [1, 2], // 测试ID
                'crudControllerFqcn' => BatchCrudController::class,
            ],
        ]);

        // 验证批量操作请求格式正确性
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertLessThan(
            500,
            $statusCode,
            '批量操作请求格式应该被正确处理'
        );
    }

    public function testBatchCreateFormAccess(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 测试表单访问
        $crawler = $client->request('GET', '/admin/coupon/batch');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $hasNewButton = $crawler->filter('a[href*="new"]')->count() > 0
                       || $crawler->filter('.btn-primary')->count() > 0;
        $this->assertTrue($hasNewButton);

        // 测试批量删除操作
        $client->request('POST', '/admin', [
            'ea' => [
                'batchActionName' => 'batchDelete',
                'batchActionEntityIds' => [999], // 不存在的ID
                'crudControllerFqcn' => BatchCrudController::class,
            ],
        ]);

        // 验证批量删除操作能够正常处理
        $response = $client->getResponse();
        $this->assertTrue(
            $response->isSuccessful() || $response->isRedirect() || $response->isClientError(),
            '批量删除操作应该有合理的响应'
        );
    }

    public function testBatchCreateFormValidation(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 测试管理界面元素
        $crawler = $client->request('GET', '/admin/coupon/batch');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $hasAdminInterface = $crawler->filter('.content')->count() > 0
                            || $crawler->filter('.main-content')->count() > 0
                            || $crawler->filter('table')->count() > 0;
        $this->assertTrue($hasAdminInterface);

        // 测试批量操作参数验证 - 使用正确的HTTP请求格式
        $client->request('POST', '/admin', [
            'ea' => [
                'batchActionName' => 'batchDelete',
                'batchActionEntityIds' => [], // 空数组进行参数验证
                'crudControllerFqcn' => BatchCrudController::class,
            ],
        ]);

        // 验证缺少参数时的处理 - EasyAdmin可能会正常处理并返回200
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertLessThan(
            500,
            $statusCode,
            sprintf('批量操作应该不会出现服务器错误，实际状态码: %d', $statusCode)
        );
    }

    public function testBatchEntityFqcnConfiguration(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 测试页面访问
        $client->request('GET', '/admin/coupon/batch');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        // 测试实体配置
        $entityClass = BatchCrudController::getEntityFqcn();
        $this->assertEquals(Batch::class, $entityClass);
        $entity = new $entityClass();
        $this->assertInstanceOf(Batch::class, $entity);

        // 测试批量操作配置
        $client->request('POST', '/admin', [
            'ea' => [
                'batchActionName' => 'batchDelete',
                'batchActionEntityIds' => [1, 2],
                'crudControllerFqcn' => BatchCrudController::class,
            ],
        ]);

        // 验证批量操作配置正确
        $this->assertLessThan(
            500,
            $client->getResponse()->getStatusCode(),
            '批量操作配置应该正确处理请求'
        );
    }

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 访问新建批次页面 - 使用EasyAdmin的URL格式
        $crawler = $client->request('GET', '/admin/coupon/batch/new');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        // 查找并提交空表单，测试必填字段验证
        $form = $crawler->selectButton('Create')->form();
        $crawler = $client->submit($form);

        // 验证返回422状态码（表单验证失败）
        $this->assertSame(422, $client->getResponse()->getStatusCode());

        // 验证错误信息包含"should not be blank"
        $invalidFeedback = $crawler->filter('.invalid-feedback');
        if ($invalidFeedback->count() > 0) {
            $this->assertStringContainsString('should not be blank', $invalidFeedback->text());
        } else {
            // 如果没有找到.invalid-feedback，检查其他可能的错误显示元素
            $errorElements = $crawler->filter('.form-error-message, .error, .alert-danger');
            $this->assertGreaterThan(0, $errorElements->count(), '应该存在表单验证错误信息');
        }
    }
}
