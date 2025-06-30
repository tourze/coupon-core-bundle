<?php

namespace Tourze\CouponCoreBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Repository\CouponRepository;

class CouponRepositoryTest extends TestCase
{
    public function testGetClassName(): void
    {
        // 通过反射验证 Repository 构造函数传递的实体类
        $reflection = new \ReflectionClass(CouponRepository::class);
        $constructor = $reflection->getConstructor();
        $this->assertNotNull($constructor);
        
        // 读取构造函数源代码
        $fileName = $constructor->getFileName();
        $startLine = $constructor->getStartLine();
        $endLine = $constructor->getEndLine();
        
        $source = file($fileName, FILE_IGNORE_NEW_LINES);
        $constructorLines = array_slice($source, $startLine - 1, $endLine - $startLine + 1);
        $constructorSource = implode("\n", $constructorLines);
        
        // 验证构造函数中调用父类时传递了正确的实体类
        $this->assertStringContainsString('parent::__construct', $constructorSource);
        $this->assertStringContainsString('Coupon::class', $constructorSource);
    }
}