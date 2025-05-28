<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\Code;

class CodeBoundaryTest extends TestCase
{
    private Code $code;
    
    protected function setUp(): void
    {
        $this->code = new Code();
    }
    
    public function test_null_values_handling(): void
    {
        // 测试所有可为 null 的字段
        $this->code->setCoupon(null);
        $this->code->setChannel(null);
        $this->code->setOwner(null);
        $this->code->setGatherChannel(null);
        $this->code->setUseChannel(null);
        $this->code->setGatherTime(null);
        $this->code->setExpireTime(null);
        $this->code->setUseTime(null);
        $this->code->setActiveTime(null);
        $this->code->setRemark(null);
        $this->code->setNeedActive(null);
        $this->code->setActive(null);
        $this->code->setCreatedBy(null);
        $this->code->setUpdatedBy(null);
        
        $this->assertNull($this->code->getCoupon());
        $this->assertNull($this->code->getChannel());
        $this->assertNull($this->code->getOwner());
        $this->assertNull($this->code->getGatherChannel());
        $this->assertNull($this->code->getUseChannel());
        $this->assertNull($this->code->getGatherTime());
        $this->assertNull($this->code->getExpireTime());
        $this->assertNull($this->code->getUseTime());
        $this->assertNull($this->code->getActiveTime());
        $this->assertNull($this->code->getRemark());
        $this->assertNull($this->code->isNeedActive());
        $this->assertNull($this->code->isActive());
        $this->assertNull($this->code->getCreatedBy());
        $this->assertNull($this->code->getUpdatedBy());
    }
    
    public function test_empty_string_values(): void
    {
        $this->code->setSn('');
        $this->code->setGatherChannel('');
        $this->code->setUseChannel('');
        $this->code->setRemark('');
        $this->code->setCreatedBy('');
        $this->code->setUpdatedBy('');
        
        $this->assertEquals('', $this->code->getSn());
        $this->assertEquals('', $this->code->getGatherChannel());
        $this->assertEquals('', $this->code->getUseChannel());
        $this->assertEquals('', $this->code->getRemark());
        $this->assertEquals('', $this->code->getCreatedBy());
        $this->assertEquals('', $this->code->getUpdatedBy());
    }
    
    public function test_extreme_values(): void
    {
        // 测试极值
        $this->code->setConsumeCount(0);
        $this->assertEquals(0, $this->code->getConsumeCount());
        
        $this->code->setConsumeCount(PHP_INT_MAX);
        $this->assertEquals(PHP_INT_MAX, $this->code->getConsumeCount());
        
        $this->code->setConsumeCount(-1);
        $this->assertEquals(-1, $this->code->getConsumeCount());
    }
    
    public function test_long_string_values(): void
    {
        $longString = str_repeat('A', 1000);
        
        $this->code->setSn($longString);
        $this->code->setGatherChannel($longString);
        $this->code->setUseChannel($longString);
        $this->code->setRemark($longString);
        
        $this->assertEquals($longString, $this->code->getSn());
        $this->assertEquals($longString, $this->code->getGatherChannel());
        $this->assertEquals($longString, $this->code->getUseChannel());
        $this->assertEquals($longString, $this->code->getRemark());
    }
    
    public function test_special_characters_in_strings(): void
    {
        $specialString = '特殊字符测试!@#$%^&*()_+-=[]{}|;:,.<>?/~`';
        
        $this->code->setSn($specialString);
        $this->code->setGatherChannel($specialString);
        $this->code->setUseChannel($specialString);
        $this->code->setRemark($specialString);
        $this->code->setCreatedBy($specialString);
        $this->code->setUpdatedBy($specialString);
        
        $this->assertEquals($specialString, $this->code->getSn());
        $this->assertEquals($specialString, $this->code->getGatherChannel());
        $this->assertEquals($specialString, $this->code->getUseChannel());
        $this->assertEquals($specialString, $this->code->getRemark());
        $this->assertEquals($specialString, $this->code->getCreatedBy());
        $this->assertEquals($specialString, $this->code->getUpdatedBy());
    }
} 