<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Entity\Coupon;

class CodeQrcodeTest extends TestCase
{
    private Code $code;
    
    protected function setUp(): void
    {
        $this->code = new Code();
    }
    
    public function test_get_qrcode_link(): void
    {
        $this->code->setSn('TEST_QR_CODE');
        
        $qrcodeData = $this->code->getQrcodeLink();
        $this->assertArrayHasKey('code', $qrcodeData);
        $this->assertArrayHasKey('sn', $qrcodeData);
        $this->assertArrayHasKey('t', $qrcodeData);
        $this->assertEquals('TEST_QR_CODE', $qrcodeData['code']);
        $this->assertEquals('TEST_QR_CODE', $qrcodeData['sn']);
        $this->assertIsInt($qrcodeData['t']);
    }
    
    public function test_get_qrcode_link_with_json_exception(): void
    {
        // 测试空 SN 的情况
        $this->code->setSn('');
        
        $qrcodeData = $this->code->getQrcodeLink();
        $this->assertArrayHasKey('code', $qrcodeData);
        $this->assertArrayHasKey('sn', $qrcodeData);
        $this->assertArrayHasKey('t', $qrcodeData);
        $this->assertEquals('', $qrcodeData['code']);
        $this->assertEquals('', $qrcodeData['sn']);
        $this->assertIsInt($qrcodeData['t']);
    }
    
    public function test_get_qrcode_link_normal_case(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $coupon->method('isValid')->willReturn(true);
        
        $this->code->setCoupon($coupon);
        $this->code->setSn('QRCODE_TEST_CODE');
        $this->code->setValid(true);
        
        $qrcodeData = $this->code->getQrcodeLink();
        $this->assertArrayHasKey('sn', $qrcodeData);
        $this->assertEquals('QRCODE_TEST_CODE', $qrcodeData['sn']);
    }
    
    public function test_get_valid_period_text_with_both_dates(): void
    {
        $gatherTime = new \DateTime('2024-01-01');
        $expireTime = new \DateTime('2024-01-31');
        
        $this->code->setGatherTime($gatherTime);
        $this->code->setExpireTime($expireTime);
        
        $periodText = $this->code->getValidPeriodText();
        
        $this->assertEquals('有效期:2024.01.01至2024.01.31', $periodText);
    }
    
    public function test_get_valid_period_text_with_expire_time_only(): void
    {
        $expireTime = new \DateTime('2024-01-31');
        
        $this->code->setGatherTime(null);
        $this->code->setExpireTime($expireTime);
        
        $periodText = $this->code->getValidPeriodText();
        
        $this->assertEquals('有效期:至2024.01.31', $periodText);
    }
    
    public function test_get_valid_period_text_with_no_expire_time(): void
    {
        $this->code->setExpireTime(null);
        
        $periodText = $this->code->getValidPeriodText();
        
        $this->assertNull($periodText);
    }
} 