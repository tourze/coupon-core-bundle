<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Enum\CodeStatus;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Code::class)]
final class CodeArrayTest extends AbstractEntityTestCase
{
    public function testArrayFunctionality(): void
    {
        $code = $this->createEntity();
        $this->assertInstanceOf(Code::class, $code);

        // 测试 Code 实体的数组表示功能
        $this->assertIsArray($code->retrieveApiArray());
        $this->assertIsArray($code->retrieveAdminArray());
        $this->assertInstanceOf(CodeStatus::class, $code->getStatus());
    }

    protected function createEntity(): Code
    {
        return new Code();
    }

    /**
     * @return array<array{0: string, 1: mixed}>
     */
    public static function propertiesProvider(): array
    {
        return [
            'sn' => ['sn', 'TEST_CODE_123'],
            'valid' => ['valid', true],
            'locked' => ['locked', false],
            'remark' => ['remark', '测试备注'],
        ];
    }
}
