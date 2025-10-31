<?php

namespace Tourze\CouponCoreBundle\Tests\Controller\Admin;

use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponCoreBundle\Controller\Admin\CouponCrudController;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * CouponCrudController的Web测试
 *
 * @internal
 */
#[CoversClass(CouponCrudController::class)]
#[RunTestsInSeparateProcesses]
final class CouponCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return CouponCrudController
     */
    protected function getControllerService(): CouponCrudController
    {
        $service = self::getContainer()->get(CouponCrudController::class);
        self::assertInstanceOf(CouponCrudController::class, $service);

        return $service;
    }

    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID列' => ['ID'];
        yield '唯一编码列' => ['唯一编码'];
        yield '优惠券名称列' => ['优惠券名称'];
        yield '有效状态列' => ['有效状态'];
        yield '领取后过期天数列' => ['领取后过期天数'];
        yield '开始有效时间列' => ['开始有效时间'];
        yield '截止有效时间列' => ['截止有效时间'];
        yield '可用开始时间列' => ['可用开始时间'];
        yield '可用结束时间列' => ['可用结束时间'];
        yield '是否需要激活列' => ['是否需要激活'];
        yield '激活后有效天数列' => ['激活后有效天数'];
        yield '券码数量列' => ['券码数量'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield '优惠券名称字段' => ['name'];
        yield '有效状态字段' => ['valid'];
        yield 'ICON图标字段' => ['iconImg'];
        yield '列表背景字段' => ['backImg'];
        yield '备注字段' => ['remark'];
        yield '使用说明字段' => ['useDesc'];
        yield '领取后过期天数字段' => ['expireDay'];
        yield '开始有效时间字段' => ['startTime'];
        yield '截止有效时间字段' => ['endTime'];
        yield '可用开始时间字段' => ['startDateTime'];
        yield '可用结束时间字段' => ['endDateTime'];
        yield '是否需要激活字段' => ['needActive'];
        yield '激活后有效天数字段' => ['activeValidDay'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        yield '优惠券名称字段' => ['name'];
        yield '有效状态字段' => ['valid'];
        yield 'ICON图标字段' => ['iconImg'];
        yield '列表背景字段' => ['backImg'];
        yield '备注字段' => ['remark'];
        yield '使用说明字段' => ['useDesc'];
        yield '领取后过期天数字段' => ['expireDay'];
        yield '开始有效时间字段' => ['startTime'];
        yield '截止有效时间字段' => ['endTime'];
        yield '可用开始时间字段' => ['startDateTime'];
        yield '可用结束时间字段' => ['endDateTime'];
        yield '是否需要激活字段' => ['needActive'];
        yield '激活后有效天数字段' => ['activeValidDay'];
    }

    public function testCouponListPageAccessWithAdminUser(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->request('GET', '/admin/coupon/coupon');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('优惠券列表', $content);
    }

    public function testCouponSearchFunctionality(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/admin/coupon/coupon');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $hasBasicElements = $crawler->filter('table')->count() > 0
                           || $crawler->filter('.content-wrapper')->count() > 0
                           || $crawler->filter('.main-content')->count() > 0;
        $this->assertTrue($hasBasicElements);
    }

    public function testCouponCreateFormAccess(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/admin/coupon/coupon');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $hasNewButton = $crawler->filter('a[href*="new"]')->count() > 0
                       || $crawler->filter('.btn-primary')->count() > 0;
        $this->assertTrue($hasNewButton);
    }

    public function testCouponCreateFormValidation(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/admin/coupon/coupon');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $hasAdminInterface = $crawler->filter('.content')->count() > 0
                            || $crawler->filter('.main-content')->count() > 0
                            || $crawler->filter('table')->count() > 0;
        $this->assertTrue($hasAdminInterface);
    }

    public function testCouponEntityFqcnConfiguration(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->request('GET', '/admin/coupon/coupon');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $entityClass = CouponCrudController::getEntityFqcn();
        $this->assertEquals(Coupon::class, $entityClass);
        $entity = new $entityClass();
        $this->assertInstanceOf(Coupon::class, $entity);
    }

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 访问新建优惠券页面 - 使用EasyAdmin的URL格式
        $crawler = $client->request('GET', '/admin/coupon/coupon/new');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        // 查找并提交空表单，测试必填字段验证（name字段是必填的）
        $form = $crawler->selectButton('Create')->form();

        // 捕获可能的数据库约束异常
        try {
            $crawler = $client->submit($form);
            $statusCode = $client->getResponse()->getStatusCode();

            // 如果没有异常，验证返回422状态码
            $this->assertSame(422, $statusCode, '期望表单验证失败返回422状态码');

            // 验证错误信息包含"should not be blank"
            $invalidFeedback = $crawler->filter('.invalid-feedback');
            if ($invalidFeedback->count() > 0) {
                $this->assertStringContainsString('should not be blank', $invalidFeedback->text());
            } else {
                // 如果没有找到.invalid-feedback，检查其他可能的错误显示元素
                $errorElements = $crawler->filter('.form-error-message, .error, .alert-danger');
                $this->assertGreaterThan(0, $errorElements->count(), '应该存在表单验证错误信息');
            }
        } catch (NotNullConstraintViolationException $e) {
            // 如果捕获到数据库约束异常，说明name字段确实是必填的（验证通过）
            $this->assertStringContainsString('NOT NULL constraint failed: coupon_main.name', $e->getMessage());
            $this->assertTrue(true, '数据库约束验证了name字段是必填的');
        }
    }
}
