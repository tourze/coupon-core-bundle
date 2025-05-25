<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\Category;
use Tourze\CouponCoreBundle\Entity\Coupon;

class CategoryTest extends TestCase
{
    private Category $category;
    
    protected function setUp(): void
    {
        $this->category = new Category();
    }
    
    public function testInstanceCreation(): void
    {
        $this->assertInstanceOf(Category::class, $this->category);
    }
    
    public function testGetterAndSetterMethods(): void
    {
        // 测试基本属性设置和获取
        $this->category->setTitle('测试分类');
        $this->assertEquals('测试分类', $this->category->getTitle());
        
        $this->category->setDescription('测试描述');
        $this->assertEquals('测试描述', $this->category->getDescription());
        
        $this->category->setLogoUrl('https://example.com/logo.png');
        $this->assertEquals('https://example.com/logo.png', $this->category->getLogoUrl());
        
        $this->category->setRemark('测试备注');
        $this->assertEquals('测试备注', $this->category->getRemark());
        
        $this->category->setSortNumber(10);
        $this->assertEquals(10, $this->category->getSortNumber());
        
        $tags = ['tag1', 'tag2'];
        $this->category->setShowTags($tags);
        $this->assertEquals($tags, $this->category->getShowTags());
        
        $this->category->setValid(true);
        $this->assertTrue($this->category->isValid());
        
        $this->category->setCreatedBy('admin');
        $this->assertEquals('admin', $this->category->getCreatedBy());
        
        $this->category->setUpdatedBy('admin');
        $this->assertEquals('admin', $this->category->getUpdatedBy());
        
        $now = new \DateTime();
        $this->category->setCreateTime($now);
        $this->assertEquals($now, $this->category->getCreateTime());
        
        $updateTime = new \DateTime();
        $this->category->setUpdateTime($updateTime);
        $this->assertEquals($updateTime, $this->category->getUpdateTime());
        
        $startTime = new \DateTime();
        $this->category->setStartTime($startTime);
        $this->assertEquals($startTime, $this->category->getStartTime());
        
        $endTime = new \DateTime();
        $this->category->setEndTime($endTime);
        $this->assertEquals($endTime, $this->category->getEndTime());
    }
    
    public function testParentChildRelationship(): void
    {
        $parent = new Category();
        $parent->setTitle('父分类');
        
        $child = new Category();
        $child->setTitle('子分类');
        
        // 测试设置父分类
        $child->setParent($parent);
        $this->assertSame($parent, $child->getParent());
        
        // 测试添加子分类
        $parent->addChild($child);
        $this->assertTrue($parent->getChildren()->contains($child));
        
        // 测试移除子分类
        $parent->removeChild($child);
        $this->assertFalse($parent->getChildren()->contains($child));
    }
    
    public function testCouponRelationship(): void
    {
        $coupon = new Coupon();
        
        // 测试添加优惠券
        $this->category->addCoupon($coupon);
        $this->assertTrue($this->category->getCoupons()->contains($coupon));
        
        // 测试移除优惠券
        $this->category->removeCoupon($coupon);
        $this->assertFalse($this->category->getCoupons()->contains($coupon));
    }
    
    public function testToStringMethod(): void
    {
        $this->category->setTitle('测试分类');
        
        // 当ID为空时
        $this->assertEquals('', (string)$this->category);
        
        // 使用反射设置ID
        $reflection = new \ReflectionClass($this->category);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->category, 123);
        
        $this->assertEquals('#123 测试分类', (string)$this->category);
    }
    
    public function testToSelectItem(): void
    {
        // 使用反射设置ID
        $reflection = new \ReflectionClass($this->category);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->category, 123);
        
        $this->category->setTitle('测试分类');
        
        $expected = [
            'value' => 123,
            'text' => '#123 测试分类',
            'label' => '#123 测试分类',
        ];
        
        $this->assertEquals($expected, $this->category->toSelectItem());
    }
    
    public function testGetNestTitle(): void
    {
        $parent = new Category();
        $parent->setTitle('父分类');
        
        $child = new Category();
        $child->setTitle('子分类');
        $child->setParent($parent);
        
        // 子分类的嵌套标题应该包含父分类和子分类名称
        $this->assertEquals('父分类/子分类', $child->getNestTitle());
        
        // 父分类的嵌套标题应该只包含自己的名称
        $this->assertEquals('父分类', $parent->getNestTitle());
    }
    
    public function testRetrieveSortableArray(): void
    {
        $this->category->setSortNumber(10);
        
        $expected = [
            'sortNumber' => 10,
        ];
        
        $this->assertEquals($expected, $this->category->retrieveSortableArray());
    }
    
    public function testRetrieveAdminArray(): void
    {
        $this->category->setTitle('测试分类');
        $this->category->setDescription('测试描述');
        
        $adminArray = $this->category->retrieveAdminArray();
        
        $this->assertIsArray($adminArray);
        $this->assertEquals('测试分类', $adminArray['title']);
        $this->assertEquals('测试描述', $adminArray['description']);
    }
    
    public function testRetrieveReadArray(): void
    {
        $this->category->setTitle('测试分类');
        $this->category->setDescription('测试描述');
        
        $readArray = $this->category->retrieveReadArray();
        
        $this->assertIsArray($readArray);
        $this->assertEquals('测试分类', $readArray['title']);
        $this->assertEquals('测试描述', $readArray['description']);
    }
    
    public function testGetSimpleArray(): void
    {
        // 使用反射设置ID
        $reflection = new \ReflectionClass($this->category);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->category, 123);
        
        $this->category->setTitle('测试分类');
        $this->category->setLogoUrl('https://example.com/logo.png');
        
        $simpleArray = $this->category->getSimpleArray();
        
        $this->assertIsArray($simpleArray);
        $this->assertEquals(123, $simpleArray['id']);
        $this->assertEquals('测试分类', $simpleArray['title']);
        $this->assertEquals('https://example.com/logo.png', $simpleArray['logoUrl']);
    }
}
