<?php

namespace Tourze\CouponCoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\CouponCoreBundle\Entity\Attribute;
use Tourze\CouponCoreBundle\Entity\Coupon;

/**
 * 优惠券属性数据填充
 * 为优惠券添加自定义属性数据
 */
class AttributeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // 获取优惠券引用
        $discount20Coupon = $this->getReference(CouponFixtures::COUPON_DISCOUNT_20, Coupon::class);
        $discount50Coupon = $this->getReference(CouponFixtures::COUPON_DISCOUNT_50, Coupon::class);
        $percent10Coupon = $this->getReference(CouponFixtures::COUPON_PERCENT_10, Coupon::class);
        $percent15Coupon = $this->getReference(CouponFixtures::COUPON_PERCENT_15, Coupon::class);
        $freeShippingCoupon = $this->getReference(CouponFixtures::COUPON_FREE_SHIPPING, Coupon::class);

        // 为服装满减券添加属性
        $this->createAttribute($manager, $discount20Coupon, 'discount_type', 'fixed', '固定金额折扣');
        $this->createAttribute($manager, $discount20Coupon, 'discount_amount', '20', '折扣金额20元');
        $this->createAttribute($manager, $discount20Coupon, 'min_amount', '100', '最低消费100元');
        $this->createAttribute($manager, $discount20Coupon, 'category_limit', 'clothes', '仅限服装类商品');
        $this->createAttribute($manager, $discount20Coupon, 'max_usage_per_user', '1', '每用户限用1次');

        // 为数码产品满减券添加属性
        $this->createAttribute($manager, $discount50Coupon, 'discount_type', 'fixed', '固定金额折扣');
        $this->createAttribute($manager, $discount50Coupon, 'discount_amount', '50', '折扣金额50元');
        $this->createAttribute($manager, $discount50Coupon, 'min_amount', '300', '最低消费300元');
        $this->createAttribute($manager, $discount50Coupon, 'category_limit', 'electronics', '仅限数码电器类商品');
        $this->createAttribute($manager, $discount50Coupon, 'requires_activation', 'true', '需要激活才能使用');
        $this->createAttribute($manager, $discount50Coupon, 'max_usage_per_user', '1', '每用户限用1次');

        // 为餐厅折扣券添加属性
        $this->createAttribute($manager, $percent10Coupon, 'discount_type', 'percentage', '百分比折扣');
        $this->createAttribute($manager, $percent10Coupon, 'discount_rate', '0.9', '9折优惠');
        $this->createAttribute($manager, $percent10Coupon, 'max_discount', '100', '最高优惠100元');
        $this->createAttribute($manager, $percent10Coupon, 'usage_location', 'dine_in', '仅限堂食使用');
        $this->createAttribute($manager, $percent10Coupon, 'holiday_valid', 'true', '节假日可用');

        // 为外卖折扣券添加属性
        $this->createAttribute($manager, $percent15Coupon, 'discount_type', 'percentage', '百分比折扣');
        $this->createAttribute($manager, $percent15Coupon, 'discount_rate', '0.85', '85折优惠');
        $this->createAttribute($manager, $percent15Coupon, 'min_amount', '20', '最低消费20元');
        $this->createAttribute($manager, $percent15Coupon, 'max_discount', '30', '最高优惠30元');
        $this->createAttribute($manager, $percent15Coupon, 'usage_location', 'delivery', '仅限外卖使用');
        $this->createAttribute($manager, $percent15Coupon, 'daily_limit', '1', '每日限用1次');

        // 为免配送费券添加属性
        $this->createAttribute($manager, $freeShippingCoupon, 'discount_type', 'free_shipping', '免配送费');
        $this->createAttribute($manager, $freeShippingCoupon, 'shipping_value', '5', '免除5元配送费');
        $this->createAttribute($manager, $freeShippingCoupon, 'min_amount', '0', '无最低消费限制');
        $this->createAttribute($manager, $freeShippingCoupon, 'usage_location', 'delivery', '仅限外卖配送');
        $this->createAttribute($manager, $freeShippingCoupon, 'expires_same_day', 'true', '当日有效');

        $manager->flush();
    }

    /**
     * 创建属性的辅助方法
     */
    private function createAttribute(ObjectManager $manager, Coupon $coupon, string $name, string $value, string $remark): void
    {
        $attribute = new Attribute();
        $attribute->setCoupon($coupon);
        $attribute->setName($name);
        $attribute->setValue($value);
        $attribute->setRemark($remark);

        $manager->persist($attribute);
    }

    public function getDependencies(): array
    {
        return [
            CouponFixtures::class,
        ];
    }
} 