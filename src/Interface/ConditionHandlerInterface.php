<?php

namespace Tourze\CouponCoreBundle\Interface;

use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\ValueObject\ConditionContext;
use Tourze\CouponCoreBundle\ValueObject\FormField;
use Tourze\CouponCoreBundle\ValueObject\ValidationResult;

/**
 * 条件处理器接口
 */
interface ConditionHandlerInterface
{
    /**
     * 获取条件类型标识符
     */
    public function getType(): string;

    /**
     * 获取条件类型显示名称
     */
    public function getLabel(): string;

    /**
     * 获取条件描述
     */
    public function getDescription(): string;

    /**
     * 获取表单字段配置
     *
     * @return iterable<FormField>
     */
    public function getFormFields(): iterable;

    /**
     * 验证条件配置的有效性
     */
    public function validateConfig(array $config): ValidationResult;

    /**
     * 创建条件实体
     */
    public function createCondition(Coupon $coupon, array $config): ConditionInterface;

    /**
     * 更新条件实体
     */
    public function updateCondition(ConditionInterface $condition, array $config): void;

    /**
     * 验证条件是否满足
     */
    public function validate(ConditionInterface $condition, ConditionContext $context): ValidationResult;

    /**
     * 获取条件的显示文本
     */
    public function getDisplayText(ConditionInterface $condition): string;

    /**
     * 获取支持的应用场景（领取/使用）
     */
    public function getSupportedScenarios(): array;
}
