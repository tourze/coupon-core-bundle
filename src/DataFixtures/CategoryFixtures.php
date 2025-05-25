<?php

namespace Tourze\CouponCoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Tourze\CouponCoreBundle\Entity\Category;

/**
 * 优惠券分类数据填充
 * 创建测试用的分类数据，包含根分类和子分类
 */
class CategoryFixtures extends Fixture
{
    // 分类引用常量
    public const ROOT_CATEGORY_SHOPPING = 'root-category-shopping';
    public const ROOT_CATEGORY_FOOD = 'root-category-food';
    public const SUB_CATEGORY_CLOTHES = 'sub-category-clothes';
    public const SUB_CATEGORY_ELECTRONICS = 'sub-category-electronics';
    public const SUB_CATEGORY_RESTAURANT = 'sub-category-restaurant';
    public const SUB_CATEGORY_TAKEAWAY = 'sub-category-takeaway';

    public function load(ObjectManager $manager): void
    {
        // 创建购物根分类
        $shoppingCategory = new Category();
        $shoppingCategory->setTitle('购物优惠');
        $shoppingCategory->setDescription('各类购物场景的优惠券分类');
        $shoppingCategory->setLogoUrl('https://images.unsplash.com/photo-1472851294608-062f824d29cc?w=400');
        $shoppingCategory->setValid(true);
        $shoppingCategory->setSortNumber(100);
        $shoppingCategory->setShowTags(['热门', '推荐']);
        $shoppingCategory->setStartTime(new \DateTime('2024-01-01'));
        $shoppingCategory->setEndTime(new \DateTime('2024-12-31'));

        $manager->persist($shoppingCategory);

        // 创建美食根分类
        $foodCategory = new Category();
        $foodCategory->setTitle('美食优惠');
        $foodCategory->setDescription('餐饮美食相关的优惠券分类');
        $foodCategory->setLogoUrl('https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=400');
        $foodCategory->setValid(true);
        $foodCategory->setSortNumber(90);
        $foodCategory->setShowTags(['美食', '热门']);
        $foodCategory->setStartTime(new \DateTime('2024-01-01'));
        $foodCategory->setEndTime(new \DateTime('2024-12-31'));

        $manager->persist($foodCategory);

        // 创建服装子分类
        $clothesCategory = new Category();
        $clothesCategory->setTitle('服装鞋帽');
        $clothesCategory->setDescription('服装、鞋子、帽子等穿戴用品优惠');
        $clothesCategory->setLogoUrl('https://images.unsplash.com/photo-1445205170230-053b83016050?w=400');
        $clothesCategory->setParent($shoppingCategory);
        $clothesCategory->setValid(true);
        $clothesCategory->setSortNumber(80);
        $clothesCategory->setShowTags(['时尚', '新品']);

        $manager->persist($clothesCategory);

        // 创建电子产品子分类
        $electronicsCategory = new Category();
        $electronicsCategory->setTitle('数码电器');
        $electronicsCategory->setDescription('手机、电脑、家电等数码产品优惠');
        $electronicsCategory->setLogoUrl('https://images.unsplash.com/photo-1468495244123-6c6c332eeece?w=400');
        $electronicsCategory->setParent($shoppingCategory);
        $electronicsCategory->setValid(true);
        $electronicsCategory->setSortNumber(70);
        $electronicsCategory->setShowTags(['科技', '热销']);

        $manager->persist($electronicsCategory);

        // 创建餐厅子分类
        $restaurantCategory = new Category();
        $restaurantCategory->setTitle('餐厅堂食');
        $restaurantCategory->setDescription('餐厅堂食用餐优惠券');
        $restaurantCategory->setLogoUrl('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=400');
        $restaurantCategory->setParent($foodCategory);
        $restaurantCategory->setValid(true);
        $restaurantCategory->setSortNumber(60);
        $restaurantCategory->setShowTags(['堂食', '环境']);

        $manager->persist($restaurantCategory);

        // 创建外卖子分类
        $takeawayCategory = new Category();
        $takeawayCategory->setTitle('外卖配送');
        $takeawayCategory->setDescription('外卖配送服务优惠券');
        $takeawayCategory->setLogoUrl('https://images.unsplash.com/photo-1526367790999-0150786686a2?w=400');
        $takeawayCategory->setParent($foodCategory);
        $takeawayCategory->setValid(true);
        $takeawayCategory->setSortNumber(50);
        $takeawayCategory->setShowTags(['配送', '便捷']);

        $manager->persist($takeawayCategory);

        $manager->flush();

        // 添加引用供其他Fixture使用
        $this->addReference(self::ROOT_CATEGORY_SHOPPING, $shoppingCategory);
        $this->addReference(self::ROOT_CATEGORY_FOOD, $foodCategory);
        $this->addReference(self::SUB_CATEGORY_CLOTHES, $clothesCategory);
        $this->addReference(self::SUB_CATEGORY_ELECTRONICS, $electronicsCategory);
        $this->addReference(self::SUB_CATEGORY_RESTAURANT, $restaurantCategory);
        $this->addReference(self::SUB_CATEGORY_TAKEAWAY, $takeawayCategory);
    }
} 