<?php

namespace Tourze\CouponCoreBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponCoreBundle\Controller\Admin\CouponStatCrudController;
use Tourze\CouponCoreBundle\Entity\CouponStat;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * CouponStatCrudController的Web测试
 *
 * @internal
 */
#[CoversClass(CouponStatCrudController::class)]
#[RunTestsInSeparateProcesses]
final class CouponStatCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return CouponStatCrudController
     */
    protected function getControllerService(): CouponStatCrudController
    {
        $service = self::getContainer()->get(CouponStatCrudController::class);
        self::assertInstanceOf(CouponStatCrudController::class, $service);

        return $service;
    }

    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID列' => ['ID'];
        yield '优惠券ID列' => ['优惠券ID'];
        yield '总数量列' => ['总数量'];
        yield '已领取数量列' => ['已领取数量'];
        yield '已使用数量列' => ['已使用数量'];
        yield '已过期数量列' => ['已过期数量'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        yield '优惠券ID字段' => ['couponId'];
        yield '总数量字段' => ['totalNum'];
        yield '已领取数量字段' => ['receivedNum'];
        yield '已使用数量字段' => ['usedNum'];
        yield '已过期数量字段' => ['expiredNum'];
    }

    public function testCouponStatListPageAccessWithAdminUser(): void
    {
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/admin/coupon/couponstat');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('优惠券统计列表', $content);
    }

    public function testCouponStatSearchFunctionality(): void
    {
        $client = self::createAuthenticatedClient();

        $crawler = $client->request('GET', '/admin/coupon/couponstat');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $hasBasicElements = $crawler->filter('table')->count() > 0
                           || $crawler->filter('.content-wrapper')->count() > 0
                           || $crawler->filter('.main-content')->count() > 0;
        $this->assertTrue($hasBasicElements);
    }

    public function testCouponStatCreateFormAccess(): void
    {
        $client = self::createAuthenticatedClient();

        // CouponStatCrudController 禁用了 NEW action，现在会抛出异常
        $this->expectException(ForbiddenActionException::class);
        $this->expectExceptionMessage('You don\'t have enough permissions to run the "new" action');

        $client->request('GET', '/admin/coupon/couponstat/new');
    }

    public function testCouponStatCreateFormValidation(): void
    {
        $client = self::createAuthenticatedClient();

        // CouponStatCrudController 禁用了 NEW action，现在会抛出异常
        $this->expectException(ForbiddenActionException::class);
        $this->expectExceptionMessage('You don\'t have enough permissions to run the "new" action');

        $client->request('GET', '/admin/coupon/couponstat/new');
    }

    public function testCouponStatEntityFqcnConfiguration(): void
    {
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/admin/coupon/couponstat');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $entityClass = CouponStatCrudController::getEntityFqcn();
        $this->assertEquals(CouponStat::class, $entityClass);
        $entity = new $entityClass();
        $this->assertInstanceOf(CouponStat::class, $entity);
    }

    public function testValidationErrors(): void
    {
        $client = self::createAuthenticatedClient();

        // CouponStatCrudController 禁用了 NEW action，现在会抛出异常
        $this->expectException(ForbiddenActionException::class);
        $this->expectExceptionMessage('You don\'t have enough permissions to run the "new" action');

        $client->request('GET', '/admin/coupon/couponstat/new');
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield '优惠券ID字段' => ['couponId'];
        yield '总数量字段' => ['totalNum'];
        yield '已领取数量字段' => ['receivedNum'];
        yield '已使用数量字段' => ['usedNum'];
        yield '已过期数量字段' => ['expiredNum'];
    }
}
