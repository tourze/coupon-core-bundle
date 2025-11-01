<?php

namespace Tourze\CouponCoreBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponCoreBundle\Controller\Admin\CodeCrudController;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * CodeCrudController的Web测试
 *
 * @internal
 */
#[CoversClass(CodeCrudController::class)]
#[RunTestsInSeparateProcesses]
final class CodeCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return CodeCrudController
     */
    protected function getControllerService(): CodeCrudController
    {
        $service = self::getContainer()->get(CodeCrudController::class);
        self::assertInstanceOf(CodeCrudController::class, $service);

        return $service;
    }

    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID列' => ['ID'];
        yield '券码列' => ['券码'];
        yield '优惠券列' => ['优惠券'];
        yield '拥有用户列' => ['拥有用户'];
        yield '状态列' => ['状态'];
        yield '核销次数列' => ['核销次数'];
        yield '领取时间列' => ['领取时间'];
        yield '过期时间列' => ['过期时间'];
        yield '使用时间列' => ['使用时间'];
        yield '激活时间列' => ['激活时间'];
        yield '需要激活列' => ['需要激活'];
        yield '已激活列' => ['已激活'];
        yield '有效列' => ['有效'];
        yield '锁定列' => ['锁定'];
        yield '创建时间列' => ['创建时间'];
        yield '更新时间列' => ['更新时间'];
        yield '创建人列' => ['创建人'];
        yield '更新人列' => ['更新人'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield '券码字段' => ['sn'];
        yield '优惠券字段' => ['coupon'];
        yield '拥有用户字段' => ['owner'];
        yield '核销次数字段' => ['consumeCount'];
        yield '备注字段' => ['remark'];
        yield '领取时间字段' => ['gatherTime'];
        yield '过期时间字段' => ['expireTime'];
        yield '使用时间字段' => ['useTime'];
        yield '激活时间字段' => ['activeTime'];
        yield '需要激活字段' => ['needActive'];
        yield '已激活字段' => ['active'];
        yield '有效字段' => ['valid'];
        yield '锁定字段' => ['locked'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        yield '券码字段' => ['sn'];
        yield '优惠券字段' => ['coupon'];
        yield '拥有用户字段' => ['owner'];
        yield '核销次数字段' => ['consumeCount'];
        yield '备注字段' => ['remark'];
        yield '领取时间字段' => ['gatherTime'];
        yield '过期时间字段' => ['expireTime'];
        yield '使用时间字段' => ['useTime'];
        yield '激活时间字段' => ['activeTime'];
        yield '需要激活字段' => ['needActive'];
        yield '已激活字段' => ['active'];
        yield '有效字段' => ['valid'];
        yield '锁定字段' => ['locked'];
    }

    public function testCodeListPageAccessWithAdminUser(): void
    {
        $client = self::createAuthenticatedClient();

        $response = $client->request('GET', '/admin/coupon/code');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('券码列表', $content);
    }

    public function testCodeSearchFunctionality(): void
    {
        $client = self::createAuthenticatedClient();

        $crawler = $client->request('GET', '/admin/coupon/code');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $hasBasicElements = $crawler->filter('table')->count() > 0
                           || $crawler->filter('.content-wrapper')->count() > 0
                           || $crawler->filter('.main-content')->count() > 0;
        $this->assertTrue($hasBasicElements);
    }

    public function testCodeCreateFormAccess(): void
    {
        $client = self::createAuthenticatedClient();

        // CodeCrudController 禁用了 NEW action，现在会抛出异常
        $this->expectException(ForbiddenActionException::class);
        $this->expectExceptionMessage('You don\'t have enough permissions to run the "new" action');

        $client->request('GET', '/admin/coupon/code/new');
    }

    public function testCodeCreateFormValidation(): void
    {
        $client = self::createAuthenticatedClient();

        // CodeCrudController 禁用了 NEW action，现在会抛出异常
        $this->expectException(ForbiddenActionException::class);
        $this->expectExceptionMessage('You don\'t have enough permissions to run the "new" action');

        $client->request('GET', '/admin/coupon/code/new');
    }

    public function testCodeEntityFqcnConfiguration(): void
    {
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/admin/coupon/code');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $entityClass = CodeCrudController::getEntityFqcn();
        $this->assertEquals(Code::class, $entityClass);
        $entity = new $entityClass();
        $this->assertInstanceOf(Code::class, $entity);
    }
}
