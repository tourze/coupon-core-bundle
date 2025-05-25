<?php

namespace Tourze\CouponCoreBundle\Handler;

use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Entity\OrderAmountSatisfy;
use Tourze\CouponCoreBundle\Enum\ConditionScenario;
use Tourze\CouponCoreBundle\Exception\CouponSatisfyException;
use Tourze\CouponCoreBundle\Interface\ConditionInterface;
use Tourze\CouponCoreBundle\Interface\SatisfyHandlerInterface;
use Tourze\CouponCoreBundle\Interface\SatisfyInterface;
use Tourze\CouponCoreBundle\ValueObject\ConditionContext;
use Tourze\CouponCoreBundle\ValueObject\FormFieldFactory;
use Tourze\CouponCoreBundle\ValueObject\OrderContext;
use Tourze\CouponCoreBundle\ValueObject\ValidationResult;

/**
 * 订单金额条件处理器
 */
class OrderAmountSatisfyHandler implements SatisfyHandlerInterface
{
    public function getType(): string
    {
        return 'order_amount';
    }

    public function getLabel(): string
    {
        return '订单金额限制';
    }

    public function getDescription(): string
    {
        return '限制订单金额范围和商品分类才能使用优惠券';
    }

    public function getFormFields(): iterable
    {
        yield FormFieldFactory::decimal('minAmount', '最低订单金额')
            ->required()
            ->min(0)
            ->help('订单金额至少需要多少才能使用');

        yield FormFieldFactory::decimal('maxAmount', '最高订单金额')
            ->min(0.01)
            ->help('订单金额不超过多少才能使用，留空表示无上限');

        yield FormFieldFactory::array('includeCategories', '包含商品分类')
            ->help('只有包含这些分类的订单才能使用，留空表示不限制');

        yield FormFieldFactory::array('excludeCategories', '排除商品分类')
            ->help('包含这些分类的订单不能使用');
    }

    public function validateConfig(array $config): ValidationResult
    {
        $errors = [];

        if (!isset($config['minAmount']) || !is_numeric($config['minAmount']) || $config['minAmount'] < 0) {
            $errors[] = '最低订单金额必须是非负数';
        }

        if (isset($config['maxAmount']) && (!is_numeric($config['maxAmount']) || $config['maxAmount'] <= 0)) {
            $errors[] = '最高订单金额必须是正数';
        }

        if (isset($config['minAmount'], $config['maxAmount']) && $config['minAmount'] > $config['maxAmount']) {
            $errors[] = '最低订单金额不能大于最高订单金额';
        }

        if (isset($config['includeCategories']) && !is_array($config['includeCategories'])) {
            $errors[] = '包含商品分类必须是数组';
        }

        if (isset($config['excludeCategories']) && !is_array($config['excludeCategories'])) {
            $errors[] = '排除商品分类必须是数组';
        }

        return empty($errors) ? ValidationResult::success() : ValidationResult::failure($errors);
    }

    public function createCondition(Coupon $coupon, array $config): ConditionInterface
    {
        $satisfy = new OrderAmountSatisfy();
        $satisfy->setCoupon($coupon);
        $satisfy->setType($this->getType());
        $satisfy->setLabel($this->getLabel());
        $satisfy->setMinAmount((string) $config['minAmount']);
        
        if (isset($config['maxAmount'])) {
            $satisfy->setMaxAmount((string) $config['maxAmount']);
        }

        if (isset($config['includeCategories'])) {
            $satisfy->setIncludeCategories($config['includeCategories']);
        }

        if (isset($config['excludeCategories'])) {
            $satisfy->setExcludeCategories($config['excludeCategories']);
        }

        return $satisfy;
    }

    public function updateCondition(ConditionInterface $condition, array $config): void
    {
        if (!$condition instanceof OrderAmountSatisfy) {
            throw new \InvalidArgumentException('条件类型不匹配');
        }

        $condition->setMinAmount((string) $config['minAmount']);
        $condition->setMaxAmount(isset($config['maxAmount']) ? (string) $config['maxAmount'] : null);
        $condition->setIncludeCategories($config['includeCategories'] ?? null);
        $condition->setExcludeCategories($config['excludeCategories'] ?? null);
    }

    public function checkSatisfy(SatisfyInterface $satisfy, OrderContext $orderContext): bool
    {
        if (!$satisfy instanceof OrderAmountSatisfy) {
            return false;
        }

        $orderAmount = (float) $orderContext->getTotalAmount();

        // 检查最低金额
        if ($orderAmount < (float) $satisfy->getMinAmount()) {
            throw new CouponSatisfyException("订单金额不足{$satisfy->getMinAmount()}元");
        }

        // 检查最高金额
        if ($satisfy->getMaxAmount() && $orderAmount > (float) $satisfy->getMaxAmount()) {
            throw new CouponSatisfyException("订单金额超过{$satisfy->getMaxAmount()}元");
        }

        // 检查包含分类
        if ($satisfy->getIncludeCategories()) {
            if (!$orderContext->hasAnyCategory($satisfy->getIncludeCategories())) {
                throw new CouponSatisfyException('订单不包含指定商品分类');
            }
        }

        // 检查排除分类
        if ($satisfy->getExcludeCategories()) {
            if ($orderContext->hasAnyCategory($satisfy->getExcludeCategories())) {
                throw new CouponSatisfyException('订单包含不允许的商品分类');
            }
        }

        return true;
    }

    public function getDisplayText(ConditionInterface $condition): string
    {
        if (!$condition instanceof OrderAmountSatisfy) {
            return '';
        }

        $text = "订单金额满{$condition->getMinAmount()}元";
        
        if ($condition->getMaxAmount()) {
            $text .= "且不超过{$condition->getMaxAmount()}元";
        }

        if ($condition->getIncludeCategories()) {
            $text .= '，包含指定分类';
        }

        if ($condition->getExcludeCategories()) {
            $text .= '，排除指定分类';
        }

        return $text;
    }

    public function getSupportedScenarios(): array
    {
        return [ConditionScenario::SATISFY];
    }

    public function validate(ConditionInterface $condition, ConditionContext $context): ValidationResult
    {
        return ValidationResult::success();
    }
} 