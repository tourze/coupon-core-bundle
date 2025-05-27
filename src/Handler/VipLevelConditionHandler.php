<?php

namespace Tourze\CouponCoreBundle\Handler;

use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\ConditionSystemBundle\Enum\ConditionTrigger;
use Tourze\ConditionSystemBundle\Handler\AbstractConditionHandler;
use Tourze\ConditionSystemBundle\Interface\ConditionInterface;
use Tourze\ConditionSystemBundle\Interface\SubjectInterface;
use Tourze\ConditionSystemBundle\ValueObject\EvaluationContext;
use Tourze\ConditionSystemBundle\ValueObject\EvaluationResult;
use Tourze\ConditionSystemBundle\ValueObject\FormFieldFactory;
use Tourze\ConditionSystemBundle\ValueObject\ValidationResult;
use Tourze\CouponCoreBundle\Adapter\CouponSubject;
use Tourze\CouponCoreBundle\Adapter\UserActor;
use Tourze\CouponCoreBundle\Entity\VipLevelCondition;

/**
 * VIP等级条件处理器
 */
class VipLevelConditionHandler extends AbstractConditionHandler
{
    public function getType(): string
    {
        return 'vip_level';
    }

    public function getLabel(): string
    {
        return 'VIP等级限制';
    }

    public function getDescription(): string
    {
        return '限制用户VIP等级范围内才能领取优惠券';
    }

    public function getFormFields(): iterable
    {
        yield FormFieldFactory::integer('minLevel', '最低VIP等级')
            ->required()
            ->min(1)
            ->help('用户VIP等级至少需要多少才能领取');

        yield FormFieldFactory::integer('maxLevel', '最高VIP等级')
            ->min(1)
            ->help('用户VIP等级不超过多少才能领取，留空表示无上限');

        yield FormFieldFactory::array('allowedLevels', '允许的VIP等级')
            ->help('指定允许的VIP等级列表，留空表示按范围判断');
    }

    public function validateConfig(array $config): ValidationResult
    {
        $errors = [];

        if (!isset($config['minLevel']) || !is_int($config['minLevel']) || $config['minLevel'] < 1) {
            $errors[] = '最低VIP等级必须是正整数';
        }

        if (isset($config['maxLevel']) && (!is_int($config['maxLevel']) || $config['maxLevel'] < 1)) {
            $errors[] = '最高VIP等级必须是正整数';
        }

        if (isset($config['minLevel'], $config['maxLevel']) && $config['minLevel'] > $config['maxLevel']) {
            $errors[] = '最低VIP等级不能大于最高VIP等级';
        }

        if (isset($config['allowedLevels']) && !is_array($config['allowedLevels'])) {
            $errors[] = '允许的VIP等级必须是数组';
        }

        return empty($errors) ? ValidationResult::success() : ValidationResult::failure($errors);
    }

    public function createCondition(SubjectInterface $subject, array $config): ConditionInterface
    {
        if (!$subject instanceof CouponSubject) {
            throw new \InvalidArgumentException('主体必须是优惠券类型');
        }

        $condition = new VipLevelCondition();
        $condition->setCoupon($subject->getCoupon());
        $condition->setType($this->getType());
        $condition->setLabel($this->getLabel());
        $condition->setMinLevel($config['minLevel']);
        
        if (isset($config['maxLevel'])) {
            $condition->setMaxLevel($config['maxLevel']);
        }

        if (isset($config['allowedLevels'])) {
            $condition->setAllowedLevels($config['allowedLevels']);
        }

        return $condition;
    }

    public function updateCondition(ConditionInterface $condition, array $config): void
    {
        if (!$condition instanceof VipLevelCondition) {
            throw new \InvalidArgumentException('条件类型不匹配');
        }

        $condition->setMinLevel($config['minLevel']);
        $condition->setMaxLevel($config['maxLevel'] ?? null);
        $condition->setAllowedLevels($config['allowedLevels'] ?? null);
    }

    protected function doEvaluate(ConditionInterface $condition, EvaluationContext $context): EvaluationResult
    {
        if (!$condition instanceof VipLevelCondition) {
            return EvaluationResult::fail(['条件类型不匹配']);
        }

        $actor = $context->getActor();
        if (!$actor instanceof UserActor) {
            return EvaluationResult::fail(['执行者必须是用户类型']);
        }

        $user = $actor->getUser();
        $userLevel = $this->getUserVipLevel($user);

        if ($userLevel === null) {
            return EvaluationResult::fail(['无法获取用户VIP等级']);
        }

        // 如果指定了允许的等级列表，优先使用
        if ($condition->getAllowedLevels()) {
            if (!in_array($userLevel, $condition->getAllowedLevels(), true)) {
                return EvaluationResult::fail(['用户VIP等级不在允许范围内']);
            }
            return EvaluationResult::pass([
                'user_level' => $userLevel,
                'allowed_levels' => $condition->getAllowedLevels(),
            ]);
        }

        // 检查最低等级
        if ($userLevel < $condition->getMinLevel()) {
            return EvaluationResult::fail([
                "需要VIP{$condition->getMinLevel()}级以上才能领取"
            ]);
        }

        // 检查最高等级
        if ($condition->getMaxLevel() && $userLevel > $condition->getMaxLevel()) {
            return EvaluationResult::fail([
                "VIP等级超过{$condition->getMaxLevel()}级无法领取"
            ]);
        }

        return EvaluationResult::pass([
            'user_level' => $userLevel,
            'min_level' => $condition->getMinLevel(),
            'max_level' => $condition->getMaxLevel(),
        ]);
    }

    public function getDisplayText(ConditionInterface $condition): string
    {
        if (!$condition instanceof VipLevelCondition) {
            return '';
        }

        if ($condition->getAllowedLevels()) {
            $levels = implode('、', $condition->getAllowedLevels());
            return "VIP等级：{$levels}";
        }

        $text = "VIP{$condition->getMinLevel()}级以上";
        if ($condition->getMaxLevel()) {
            $text = "VIP{$condition->getMinLevel()}-{$condition->getMaxLevel()}级";
        }

        return $text;
    }

    public function getSupportedTriggers(): array
    {
        return [ConditionTrigger::BEFORE_ACTION];
    }

    /**
     * 获取用户VIP等级
     */
    private function getUserVipLevel(UserInterface $user): ?int
    {
        // 尝试通过反射获取VIP等级
        $reflection = new \ReflectionClass($user);
        
        // 常见的方法名
        $methods = ['getVipLevel', 'getLevel', 'getMemberLevel', 'getUserLevel'];
        
        foreach ($methods as $method) {
            if ($reflection->hasMethod($method)) {
                try {
                    $level = $user->$method();
                    if (is_int($level)) {
                        return $level;
                    }
                } catch (\Throwable) {
                    continue;
                }
            }
        }

        // 如果没有找到方法，返回默认等级1
        return 1;
    }
} 