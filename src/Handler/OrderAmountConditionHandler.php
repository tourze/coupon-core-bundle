<?php

namespace Tourze\CouponCoreBundle\Handler;

use Tourze\ConditionSystemBundle\Enum\ConditionTrigger;
use Tourze\ConditionSystemBundle\Handler\AbstractConditionHandler;
use Tourze\ConditionSystemBundle\Interface\ConditionInterface;
use Tourze\ConditionSystemBundle\Interface\SubjectInterface;
use Tourze\ConditionSystemBundle\ValueObject\EvaluationContext;
use Tourze\ConditionSystemBundle\ValueObject\EvaluationResult;
use Tourze\ConditionSystemBundle\ValueObject\FormFieldFactory;
use Tourze\ConditionSystemBundle\ValueObject\ValidationResult;
use Tourze\CouponCoreBundle\Adapter\CouponSubject;
use Tourze\CouponCoreBundle\Entity\OrderAmountCondition;
use Tourze\CouponCoreBundle\ValueObject\OrderContext;

/**
 * 订单金额条件处理器
 */
class OrderAmountConditionHandler extends AbstractConditionHandler
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

    public function createCondition(SubjectInterface $subject, array $config): ConditionInterface
    {
        if (!$subject instanceof CouponSubject) {
            throw new \InvalidArgumentException('主体必须是优惠券类型');
        }

        $condition = new OrderAmountCondition();
        $condition->setCoupon($subject->getCoupon());
        $condition->setType($this->getType());
        $condition->setLabel($this->getLabel());
        $condition->setMinAmount((string) $config['minAmount']);
        
        if (isset($config['maxAmount'])) {
            $condition->setMaxAmount((string) $config['maxAmount']);
        }

        if (isset($config['includeCategories'])) {
            $condition->setIncludeCategories($config['includeCategories']);
        }

        if (isset($config['excludeCategories'])) {
            $condition->setExcludeCategories($config['excludeCategories']);
        }

        return $condition;
    }

    public function updateCondition(ConditionInterface $condition, array $config): void
    {
        if (!$condition instanceof OrderAmountCondition) {
            throw new \InvalidArgumentException('条件类型不匹配');
        }

        $condition->setMinAmount((string) $config['minAmount']);
        $condition->setMaxAmount(isset($config['maxAmount']) ? (string) $config['maxAmount'] : null);
        $condition->setIncludeCategories($config['includeCategories'] ?? null);
        $condition->setExcludeCategories($config['excludeCategories'] ?? null);
    }

    protected function doEvaluate(ConditionInterface $condition, EvaluationContext $context): EvaluationResult
    {
        if (!$condition instanceof OrderAmountCondition) {
            return EvaluationResult::fail(['条件类型不匹配']);
        }

        // 从上下文中获取订单信息
        $payload = $context->getPayload();
        if (!$payload instanceof OrderContext) {
            return EvaluationResult::fail(['需要订单上下文信息']);
        }

        $orderAmount = (float) $payload->getTotalAmount();

        // 检查最低金额
        if ($orderAmount < (float) $condition->getMinAmount()) {
            return EvaluationResult::fail([
                "订单金额不足{$condition->getMinAmount()}元"
            ]);
        }

        // 检查最高金额
        if ($condition->getMaxAmount() && $orderAmount > (float) $condition->getMaxAmount()) {
            return EvaluationResult::fail([
                "订单金额超过{$condition->getMaxAmount()}元"
            ]);
        }

        // 检查包含分类
        if ($condition->getIncludeCategories()) {
            if (!$payload->hasAnyCategory($condition->getIncludeCategories())) {
                return EvaluationResult::fail(['订单不包含指定商品分类']);
            }
        }

        // 检查排除分类
        if ($condition->getExcludeCategories()) {
            if ($payload->hasAnyCategory($condition->getExcludeCategories())) {
                return EvaluationResult::fail(['订单包含不允许的商品分类']);
            }
        }

        return EvaluationResult::pass([
            'order_amount' => $orderAmount,
            'min_amount' => $condition->getMinAmount(),
            'max_amount' => $condition->getMaxAmount(),
            'include_categories' => $condition->getIncludeCategories(),
            'exclude_categories' => $condition->getExcludeCategories(),
        ]);
    }

    public function getDisplayText(ConditionInterface $condition): string
    {
        if (!$condition instanceof OrderAmountCondition) {
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

    public function getSupportedTriggers(): array
    {
        return [ConditionTrigger::VALIDATION];
    }
} 