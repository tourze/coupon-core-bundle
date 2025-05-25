<?php

namespace Tourze\CouponCoreBundle\Tests\Service;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\CouponCoreBundle\Service\CodeService;

class CodeServiceTest extends TestCase
{
    private CodeService $codeService;
    private CodeRepository $codeRepository;
    
    protected function setUp(): void
    {
        $this->codeRepository = $this->createMock(CodeRepository::class);
        
        $this->codeService = new CodeService(
            $this->codeRepository
        );
    }
    
    public function testGetValidStock(): void
    {
        $coupon = new Coupon();
        
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);
        
        $this->codeRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('a')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('where')
            ->with('a.owner IS NULL')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('COUNT(a.id)')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        
        $query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn('10');
        
        $result = $this->codeService->getValidStock($coupon);
        
        $this->assertEquals(10, $result);
    }
    
    public function testGetGatherStock(): void
    {
        $coupon = new Coupon();
        
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);
        
        $this->codeRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('a')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('where')
            ->with('a.owner IS NOT NULL')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('COUNT(a.id)')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        
        $query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn('5');
        
        $result = $this->codeService->getGatherStock($coupon);
        
        $this->assertEquals(5, $result);
    }
    
    public function testGetValidStockWithDatabaseError(): void
    {
        $coupon = new Coupon();
        
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);
        
        $this->codeRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('a')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('where')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('select')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        
        // 模拟数据库错误
        $query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willThrowException(new \Doctrine\ORM\NoResultException());
        
        // 应该捕获异常并返回0
        $this->expectException(\Doctrine\ORM\NoResultException::class);
        $this->codeService->getValidStock($coupon);
    }
    
    public function testGetGatherStockWithNoResult(): void
    {
        $coupon = new Coupon();
        
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);
        
        $this->codeRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('a')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('where')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('select')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        
        $query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn(null);
        
        $result = $this->codeService->getGatherStock($coupon);
        
        // 应该返回0而不是null
        $this->assertEquals(0, $result);
    }
} 