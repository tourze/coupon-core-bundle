<?php

namespace Tourze\CouponCoreBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class CouponCoreExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
