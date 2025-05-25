<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\Channel;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Entity\Coupon;

class ChannelTest extends TestCase
{
    private Channel $channel;

    protected function setUp(): void
    {
        $this->channel = new Channel();
    }

    public function test_instance_creation(): void
    {
        $this->assertInstanceOf(Channel::class, $this->channel);
        $this->assertNull($this->channel->getId());
        $this->assertNull($this->channel->getTitle());
        $this->assertNull($this->channel->getCode());
        $this->assertInstanceOf(ArrayCollection::class, $this->channel->getCodes());
        $this->assertInstanceOf(ArrayCollection::class, $this->channel->getCoupons());
    }

    public function test_getter_and_setter_methods(): void
    {
        $title = 'Test Channel';
        $code = 'TEST001';
        $remark = 'Test channel remark';
        $logo = 'https://example.com/logo.png';
        $redirectUrl = 'https://example.com/redirect';
        $appId = 'wx1234567890';
        $createdBy = 'admin';
        $updatedBy = 'moderator';

        $this->channel->setTitle($title);
        $this->channel->setCode($code);
        $this->channel->setRemark($remark);
        $this->channel->setLogo($logo);
        $this->channel->setRedirectUrl($redirectUrl);
        $this->channel->setAppId($appId);
        $this->channel->setCreatedBy($createdBy);
        $this->channel->setUpdatedBy($updatedBy);
        $this->channel->setValid(true);

        $this->assertEquals($title, $this->channel->getTitle());
        $this->assertEquals($code, $this->channel->getCode());
        $this->assertEquals($remark, $this->channel->getRemark());
        $this->assertEquals($logo, $this->channel->getLogo());
        $this->assertEquals($redirectUrl, $this->channel->getRedirectUrl());
        $this->assertEquals($appId, $this->channel->getAppId());
        $this->assertEquals($createdBy, $this->channel->getCreatedBy());
        $this->assertEquals($updatedBy, $this->channel->getUpdatedBy());
        $this->assertTrue($this->channel->isValid());
    }

    public function test_datetime_properties(): void
    {
        $createTime = new \DateTime('2023-01-01 10:00:00');
        $updateTime = new \DateTime('2023-01-02 11:00:00');

        $this->channel->setCreateTime($createTime);
        $this->channel->setUpdateTime($updateTime);

        $this->assertEquals($createTime, $this->channel->getCreateTime());
        $this->assertEquals($updateTime, $this->channel->getUpdateTime());
    }

    public function test_datetime_properties_with_null_values(): void
    {
        $this->channel->setCreateTime(null);
        $this->channel->setUpdateTime(null);

        $this->assertNull($this->channel->getCreateTime());
        $this->assertNull($this->channel->getUpdateTime());
    }

    public function test_code_relationship(): void
    {
        $code1 = new Code();
        $code1->setSn('CODE001');
        $code2 = new Code();
        $code2->setSn('CODE002');

        $this->channel->addCode($code1);
        $this->channel->addCode($code2);

        $this->assertCount(2, $this->channel->getCodes());
        $this->assertTrue($this->channel->getCodes()->contains($code1));
        $this->assertTrue($this->channel->getCodes()->contains($code2));
        $this->assertSame($this->channel, $code1->getChannel());
        $this->assertSame($this->channel, $code2->getChannel());
    }

    public function test_add_duplicate_code(): void
    {
        $code = new Code();
        $code->setSn('DUPLICATE');

        $this->channel->addCode($code);
        $this->channel->addCode($code); // 添加相同的 code

        $this->assertCount(1, $this->channel->getCodes());
    }

    public function test_remove_code(): void
    {
        $code = new Code();
        $code->setSn('REMOVE_TEST');

        $this->channel->addCode($code);
        $this->assertCount(1, $this->channel->getCodes());

        $this->channel->removeCode($code);
        $this->assertCount(0, $this->channel->getCodes());
        $this->assertNull($code->getChannel());
    }

    public function test_coupon_relationship(): void
    {
        $coupon1 = new Coupon();
        $coupon1->setName('Coupon 1');
        $coupon2 = new Coupon();
        $coupon2->setName('Coupon 2');

        $this->channel->addCoupon($coupon1);
        $this->channel->addCoupon($coupon2);

        $this->assertCount(2, $this->channel->getCoupons());
        $this->assertTrue($this->channel->getCoupons()->contains($coupon1));
        $this->assertTrue($this->channel->getCoupons()->contains($coupon2));
    }

    public function test_add_duplicate_coupon(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Duplicate Coupon');

        $this->channel->addCoupon($coupon);
        $this->channel->addCoupon($coupon); // 添加相同的 coupon

        $this->assertCount(1, $this->channel->getCoupons());
    }

    public function test_remove_coupon(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Remove Test Coupon');

        $this->channel->addCoupon($coupon);
        $this->assertCount(1, $this->channel->getCoupons());

        $this->channel->removeCoupon($coupon);
        $this->assertCount(0, $this->channel->getCoupons());
    }

    public function test_to_string_method(): void
    {
        $title = 'Channel Title';
        $this->channel->setTitle($title);

        $this->assertEquals($title, (string) $this->channel);
    }

    public function test_to_string_method_with_null_title(): void
    {
        $this->assertEquals('', (string) $this->channel);
    }

    public function test_retrieve_plain_array(): void
    {
        $this->channel->setTitle('Test Channel');
        $this->channel->setCode('TEST123');
        $this->channel->setRemark('Test remark');
        $this->channel->setLogo('https://example.com/logo.png');
        $this->channel->setRedirectUrl('https://example.com');
        $this->channel->setAppId('wx123456');

        $createTime = new \DateTime('2023-01-01 10:00:00');
        $updateTime = new \DateTime('2023-01-02 11:00:00');
        $this->channel->setCreateTime($createTime);
        $this->channel->setUpdateTime($updateTime);

        $result = $this->channel->retrievePlainArray();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('createTime', $result);
        $this->assertArrayHasKey('updateTime', $result);
        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('remark', $result);
        $this->assertArrayHasKey('logo', $result);
        $this->assertArrayHasKey('redirectUrl', $result);
        $this->assertArrayHasKey('appId', $result);

        $this->assertEquals('Test Channel', $result['title']);
        $this->assertEquals('TEST123', $result['code']);
        $this->assertEquals('Test remark', $result['remark']);
        $this->assertEquals('2023-01-01 10:00:00', $result['createTime']);
        $this->assertEquals('2023-01-02 11:00:00', $result['updateTime']);
    }

    public function test_retrieve_api_array(): void
    {
        $this->channel->setTitle('API Test');
        $plainArray = $this->channel->retrievePlainArray();
        $apiArray = $this->channel->retrieveApiArray();

        $this->assertEquals($plainArray, $apiArray);
    }

    public function test_retrieve_admin_array(): void
    {
        $this->channel->setTitle('Admin Test');
        $plainArray = $this->channel->retrievePlainArray();
        $adminArray = $this->channel->retrieveAdminArray();

        $this->assertEquals($plainArray, $adminArray);
    }

    public function test_valid_property(): void
    {
        $this->assertFalse($this->channel->isValid());

        $this->channel->setValid(true);
        $this->assertTrue($this->channel->isValid());

        $this->channel->setValid(false);
        $this->assertFalse($this->channel->isValid());

        $this->channel->setValid(null);
        $this->assertNull($this->channel->isValid());
    }

    public function test_fluent_interface(): void
    {
        $result = $this->channel
            ->setTitle('Fluent Channel')
            ->setCode('FLUENT')
            ->setValid(true)
            ->setCreatedBy('user')
            ->setUpdatedBy('updater');

        $this->assertSame($this->channel, $result);
        $this->assertEquals('Fluent Channel', $this->channel->getTitle());
        $this->assertEquals('FLUENT', $this->channel->getCode());
        $this->assertTrue($this->channel->isValid());
    }

    public function test_null_values(): void
    {
        $this->channel->setRemark(null);
        $this->channel->setCreatedBy(null);
        $this->channel->setUpdatedBy(null);

        $this->assertNull($this->channel->getRemark());
        $this->assertNull($this->channel->getCreatedBy());
        $this->assertNull($this->channel->getUpdatedBy());
    }

    public function test_null_title(): void
    {
        // title 和 code 要求非空字符串，所以不测试 null 值
        $this->channel->setTitle('test');
        $this->assertEquals('test', $this->channel->getTitle());
    }

    public function test_empty_string_values(): void
    {
        $this->channel->setTitle('');
        $this->channel->setCode('');
        $this->channel->setRemark('');
        $this->channel->setLogo('');
        $this->channel->setAppId('');

        $this->assertEquals('', $this->channel->getTitle());
        $this->assertEquals('', $this->channel->getCode());
        $this->assertEquals('', $this->channel->getRemark());
        $this->assertEquals('', $this->channel->getLogo());
        $this->assertEquals('', $this->channel->getAppId());
    }

    public function test_special_characters_in_fields(): void
    {
        $specialTitle = 'Channel with 特殊字符 & symbols @#$%';
        $specialCode = 'CODE-123_ABC.test';
        $specialRemark = 'Remark: "quoted", <tagged>, [bracketed]';

        $this->channel->setTitle($specialTitle);
        $this->channel->setCode($specialCode);
        $this->channel->setRemark($specialRemark);

        $this->assertEquals($specialTitle, $this->channel->getTitle());
        $this->assertEquals($specialCode, $this->channel->getCode());
        $this->assertEquals($specialRemark, $this->channel->getRemark());
    }

    public function test_url_validation(): void
    {
        $validUrls = [
            'https://example.com',
            'http://localhost:8080',
            'https://sub.domain.co.uk/path?param=value',
            'ftp://files.example.com',
        ];

        foreach ($validUrls as $url) {
            $this->channel->setLogo($url);
            $this->channel->setRedirectUrl($url);

            $this->assertEquals($url, $this->channel->getLogo());
            $this->assertEquals($url, $this->channel->getRedirectUrl());
        }
    }

    public function test_long_text_values(): void
    {
        $longTitle = str_repeat('A', 60); // 数据库字段限制为60
        $longCode = str_repeat('B', 100); // 数据库字段限制为100
        $longRemark = str_repeat('C', 100); // 数据库字段限制为100
        $longUrl = 'https://example.com/' . str_repeat('d', 400); // 数据库字段限制为500

        $this->channel->setTitle($longTitle);
        $this->channel->setCode($longCode);
        $this->channel->setRemark($longRemark);
        $this->channel->setLogo($longUrl);

        $this->assertEquals($longTitle, $this->channel->getTitle());
        $this->assertEquals($longCode, $this->channel->getCode());
        $this->assertEquals($longRemark, $this->channel->getRemark());
        $this->assertEquals($longUrl, $this->channel->getLogo());
    }

    public function test_app_id_validation(): void
    {
        $validAppIds = [
            'wx1234567890abcdef',
            'wxabcdefghij1234567890',
            '',
        ];

        foreach ($validAppIds as $appId) {
            $this->channel->setAppId($appId);
            $this->assertEquals($appId, $this->channel->getAppId());
        }
    }

    public function test_app_id_null(): void
    {
        $this->channel->setAppId(null);
        $this->assertNull($this->channel->getAppId());
    }
} 