<?php

namespace Tourze\CouponCoreBundle\Tests\Command;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\CouponCoreBundle\Command\RevokeExpiredCodeCommand;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(RevokeExpiredCodeCommand::class)]
#[RunTestsInSeparateProcesses]
final class RevokeExpiredCodeCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    protected function onSetUp(): void
    {
        $command = self::getService(RevokeExpiredCodeCommand::class);
        $this->commandTester = new CommandTester($command);
    }

    public function testCommandExecution(): void
    {
        $this->commandTester->execute([]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('撤销过期码', $this->commandTester->getDisplay());
    }

    public function testCommandConfiguration(): void
    {
        $command = self::getService(RevokeExpiredCodeCommand::class);

        $this->assertSame('coupon:revoke-expired-code', $command->getName());
        $this->assertSame('自动回收过期优惠券', $command->getDescription());
    }
}
