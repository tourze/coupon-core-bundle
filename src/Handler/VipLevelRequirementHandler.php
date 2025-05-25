<?php

namespace Tourze\CouponCoreBundle\Handler;

use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Entity\VipLevelRequirement;
use Tourze\CouponCoreBundle\Enum\ConditionScenario;
use Tourze\CouponCoreBundle\Exception\CouponRequirementException;
use Tourze\CouponCoreBundle\Interface\ConditionInterface;
use Tourze\CouponCoreBundle\Interface\RequirementHandlerInterface;
use Tourze\CouponCoreBundle\Interface\RequirementInterface;
use Tourze\CouponCoreBundle\ValueObject\ConditionContext;
use Tourze\CouponCoreBundle\ValueObject\FormFieldFactory;
use Tourze\CouponCoreBundle\ValueObject\ValidationResult;

/**
 * VIP等级条件处理器
 */
class VipLevelRequirementHandler implements RequirementHandlerInterface
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

    public function createCondition(Coupon $coupon, array $config): ConditionInterface
    {
        $requirement = new VipLevelRequirement();
        $requirement->setCoupon($coupon);
        $requirement->setType($this->getType());
        $requirement->setLabel($this->getLabel());
        $requirement->setMinLevel($config['minLevel']);
        
        if (isset($config['maxLevel'])) {
            $requirement->setMaxLevel($config['maxLevel']);
        }

        if (isset($config['allowedLevels'])) {
            $requirement->setAllowedLevels($config['allowedLevels']);
        }

        return $requirement;
    }

    public function updateCondition(ConditionInterface $condition, array $config): void
    {
        if (!$condition instanceof VipLevelRequirement) {
            throw new \InvalidArgumentException('条件类型不匹配');
        }

        $condition->setMinLevel($config['minLevel']);
        $condition->setMaxLevel($config['maxLevel'] ?? null);
        $condition->setAllowedLevels($config['allowedLevels'] ?? null);
    }

    public function checkRequirement(RequirementInterface $requirement, UserInterface $user, Coupon $coupon): bool
    {
        if (!$requirement instanceof VipLevelRequirement) {
            return false;
        }

        // 获取用户VIP等级
        $userLevel = $this->getUserVipLevel($user);

        if ($userLevel === null) {
            throw new CouponRequirementException('无法获取用户VIP等级');
        }

        // 如果指定了允许的等级列表，优先使用
        if ($requirement->getAllowedLevels()) {
            if (!in_array($userLevel, $requirement->getAllowedLevels(), true)) {
                throw new CouponRequirementException('用户VIP等级不在允许范围内');
            }
            return true;
        }

        // 检查最低等级
        if ($userLevel < $requirement->getMinLevel()) {
            throw new CouponRequirementException("需要VIP{$requirement->getMinLevel()}级以上才能领取");
        }

        // 检查最高等级
        if ($requirement->getMaxLevel() && $userLevel > $requirement->getMaxLevel()) {
            throw new CouponRequirementException("VIP等级超过{$requirement->getMaxLevel()}级无法领取");
        }

        return true;
    }

    public function getDisplayText(ConditionInterface $condition): string
    {
        if (!$condition instanceof VipLevelRequirement) {
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

    public function getSupportedScenarios(): array
    {
        return [ConditionScenario::REQUIREMENT];
    }

    public function validate(ConditionInterface $condition, ConditionContext $context): ValidationResult
    {
        return ValidationResult::success();
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