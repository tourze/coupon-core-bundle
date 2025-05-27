<?php

namespace Tourze\CouponCoreBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tourze\ConditionSystemBundle\Service\ConditionHandlerFactory;
use Tourze\ConditionSystemBundle\Service\ConditionManagerService;

/**
 * 测试条件系统与优惠券核心模块的集成
 */
class ConditionSystemIntegrationTest extends KernelTestCase
{
    private ConditionManagerService $conditionManager;
    private ConditionHandlerFactory $handlerFactory;

    protected static function getKernelClass(): string
    {
        return IntegrationTestKernel::class;
    }

    protected function setUp(): void
    {
        // 启动内核
        self::bootKernel();
        $container = static::getContainer();

        // 获取服务
        $this->conditionManager = $container->get('test.condition_manager');
        $this->handlerFactory = $container->get('test.condition_handler_factory');
    }

    public function testConditionManagerServiceExists(): void
    {
        // 测试条件管理服务存在
        $this->assertInstanceOf(ConditionManagerService::class, $this->conditionManager);
    }

    public function testConditionHandlerFactoryExists(): void
    {
        // 测试条件处理器工厂存在
        $this->assertInstanceOf(ConditionHandlerFactory::class, $this->handlerFactory);
    }

    public function testGetAvailableConditionTypesReturnsArray(): void
    {
        // 测试获取可用条件类型返回数组
        $types = $this->conditionManager->getAvailableConditionTypes();
        $this->assertIsArray($types);
    }

    public function testConditionHandlerFactoryHasHandlers(): void
    {
        // 测试处理器工厂有处理器
        $handlers = $this->handlerFactory->getAllHandlers();
        $this->assertIsArray($handlers);
    }

    public function testConditionSystemBundleIsLoaded(): void
    {
        // 测试条件系统 Bundle 已加载
        $container = static::getContainer();
        
        // 验证核心服务存在
        $this->assertTrue($container->has('Tourze\ConditionSystemBundle\Service\ConditionManagerService'));
        $this->assertTrue($container->has('Tourze\ConditionSystemBundle\Service\ConditionHandlerFactory'));
    }
} 