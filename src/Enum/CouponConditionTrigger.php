<?php

namespace Tourze\CouponCoreBundle\Enum;

use Tourze\ConditionSystemBundle\Enum\ConditionTrigger;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 优惠券条件触发器映射
 * 
 * 将通用的条件触发器映射到优惠券业务场景
 */
enum CouponConditionTrigger: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case REQUIREMENT = 'requirement';  // 领取条件 -> BEFORE_ACTION
    case SATISFY = 'satisfy';          // 使用条件 -> VALIDATION

    public function getLabel(): string
    {
        return match ($this) {
            self::REQUIREMENT => '领取条件',
            self::SATISFY => '使用条件',
        };
    }

    /**
     * 映射到通用条件触发器
     */
    public function toGenericTrigger(): ConditionTrigger
    {
        return match ($this) {
            self::REQUIREMENT => ConditionTrigger::BEFORE_ACTION,
            self::SATISFY => ConditionTrigger::VALIDATION,
        };
    }

    /**
     * 从通用条件触发器创建
     */
    public static function fromGenericTrigger(ConditionTrigger $trigger): ?self
    {
        return match ($trigger) {
            ConditionTrigger::BEFORE_ACTION => self::REQUIREMENT,
            ConditionTrigger::VALIDATION => self::SATISFY,
            default => null,
        };
    }
} 