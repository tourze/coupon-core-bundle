<?php

namespace Tourze\CouponCoreBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\CouponCoreBundle\Command\CheckExpiredCategoryCommand;
use Tourze\CouponCoreBundle\Repository\CategoryRepository;

class CheckExpiredCategoryCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $categoryRepository = $this->createMock(CategoryRepository::class);
        
        $queryBuilder = $this->getMockBuilder(\Doctrine\ORM\QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        $query = $this->getMockBuilder(\Doctrine\ORM\Query::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        $categoryRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('c')
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())
            ->method('where')
            ->with('c.startTime > :now or c.endTime < :now')
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with('now', $this->isInstanceOf(\DateTimeInterface::class))
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())
            ->method('set')
            ->with('c.valid', 0)
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())
            ->method('update')
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
            
        $query->expects($this->once())
            ->method('execute');
        
        $command = new CheckExpiredCategoryCommand($categoryRepository);
        
        $application = new Application();
        $application->add($command);
        
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        
        $this->assertSame(0, $commandTester->getStatusCode());
    }
}