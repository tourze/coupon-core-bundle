<?php

namespace Tourze\CouponCoreBundle\Tests\Command;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\CouponCoreBundle\Command\RevokeExpiredCodeCommand;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Repository\CodeRepository;

class RevokeExpiredCodeCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $codeRepository = $this->createMock(CodeRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        
        $queryBuilder = $this->getMockBuilder(\Doctrine\ORM\QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        $query = $this->getMockBuilder(\Doctrine\ORM\Query::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $code = $this->createMock(Code::class);
        
        $codeRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('a')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('where')
            ->with('a.useTime IS NULL')
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->exactly(3))
            ->method('andWhere')
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with('time', $this->isType('string'))
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())
            ->method('orderBy')
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())
            ->method('setMaxResults')
            ->with(500)
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
            
        $query->expects($this->once())
            ->method('toIterable')
            ->willReturn([$code]);
        
        $code->expects($this->once())
            ->method('setValid')
            ->with(false);
            
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($code);
            
        $entityManager->expects($this->once())
            ->method('flush');
        
        $command = new RevokeExpiredCodeCommand($codeRepository, $entityManager);
        
        $application = new Application();
        $application->add($command);
        
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        
        $this->assertSame(0, $commandTester->getStatusCode());
    }
}