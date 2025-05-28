<?php

namespace Tourze\CouponCoreBundle\Tests\Integration;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Tourze\ConditionSystemBundle\ConditionSystemBundle;

/**
 * 集成测试内核 - 只加载必要的服务
 */
class IntegrationTestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new DoctrineBundle();
        yield new SecurityBundle();
        yield new ConditionSystemBundle();
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        // Framework 配置
        $container->extension('framework', [
            'test' => true,
            'secret' => 'test_secret',
            'http_method_override' => false,
            'handle_all_throwables' => true,
            'php_errors' => [
                'log' => true,
            ],
            'validation' => [
                'email_validation_mode' => 'html5',
            ],
            'uid' => [
                'default_uuid_version' => 7,
                'time_based_uuid_version' => 7,
            ],
        ]);

        // Doctrine 配置 - 使用内存数据库，只映射通用条件系统实体
        $container->extension('doctrine', [
            'dbal' => [
                'driver' => 'pdo_sqlite',
                'url' => 'sqlite:///:memory:',
            ],
            'orm' => [
                'auto_generate_proxy_classes' => true,
                'controller_resolver' => [
                    'auto_mapping' => false,
                ],
                'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
                'auto_mapping' => false,
                'mappings' => [
                    'ConditionSystemBundle' => [
                        'is_bundle' => false,
                        'type' => 'attribute',
                        'dir' => __DIR__ . '/../../../condition-system-bundle/src/Entity',
                        'prefix' => 'Tourze\ConditionSystemBundle\Entity',
                    ],
                ],
            ],
        ]);

        // Security 配置
        $container->extension('security', [
            'providers' => [
                'in_memory' => [
                    'memory' => [
                        'users' => [
                            'test_user' => [
                                'password' => 'test_password',
                                'roles' => ['ROLE_USER'],
                            ],
                        ],
                    ],
                ],
            ],
            'firewalls' => [
                'main' => [
                    'provider' => 'in_memory',
                    'stateless' => true,
                ],
            ],
        ]);

        // 设置测试服务为 public
        $services = $container->services();
        $services->alias('test.condition_manager', 'Tourze\ConditionSystemBundle\Service\ConditionManagerService')
            ->public();
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/coupon_core_test_cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/coupon_core_test_logs';
    }
} 