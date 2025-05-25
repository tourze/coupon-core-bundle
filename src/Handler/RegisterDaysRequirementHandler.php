<?php

namespace Tourze\CouponCoreBundle\Handler;

use Carbon\Carbon;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Entity\RegisterDaysRequirement;
use Tourze\CouponCoreBundle\Enum\ConditionScenario;
use Tourze\CouponCoreBundle\Exception\CouponRequirementException;
use Tourze\CouponCoreBundle\Interface\ConditionInterface;
use Tourze\CouponCoreBundle\Interface\RequirementHandlerInterface;
use Tourze\CouponCoreBundle\Interface\RequirementInterface;
use Tourze\CouponCoreBundle\ValueObject\ConditionContext;
use Tourze\CouponCoreBundle\ValueObject\FormFieldFactory;
use Tourze\CouponCoreBundle\ValueObject\ValidationResult;

/**
 * 注册天数条件处理器
 */
class RegisterDaysRequirementHandler implements RequirementHandlerInterface
{
    public function getType(): string
    {
        return 'register_days';
    }

    public function getLabel(): string
    {
        return '注册天数限制';
    }

    public function getDescription(): string
    {
        return '限制用户注册天数范围内才能领取优惠券';
    }

    public function getFormFields(): iterable
    {
        yield FormFieldFactory::integer('minDays', '最少注册天数')
            ->required()
            ->min(0)
            ->help('用户注册至少需要多少天才能领取');

        yield FormFieldFactory::integer('maxDays', '最多注册天数')
            ->min(1)
            ->help('用户注册不超过多少天才能领取，留空表示无上限');
    }

    public function validateConfig(array $config): ValidationResult
    {
        $errors = [];

        if (!isset($config['minDays']) || !is_int($config['minDays']) || $config['minDays'] < 0) {
            $errors[] = '最少注册天数必须是非负整数';
        }

        if (isset($config['maxDays']) && (!is_int($config['maxDays']) || $config['maxDays'] <= 0)) {
            $errors[] = '最多注册天数必须是正整数';
        }

        if (isset($config['minDays'], $config['maxDays']) && $config['minDays'] > $config['maxDays']) {
            $errors[] = '最少注册天数不能大于最多注册天数';
        }

        return empty($errors) ? ValidationResult::success() : ValidationResult::failure($errors);
    }

    public function createCondition(Coupon $coupon, array $config): ConditionInterface
    {
        $requirement = new RegisterDaysRequirement();
        $requirement->setCoupon($coupon);
        $requirement->setType($this->getType());
        $requirement->setLabel($this->getLabel());
        $requirement->setMinDays($config['minDays']);

        if (isset($config['maxDays'])) {
            $requirement->setMaxDays($config['maxDays']);
        }

        return $requirement;
    }

    public function updateCondition(ConditionInterface $condition, array $config): void
    {
        if (!$condition instanceof RegisterDaysRequirement) {
            throw new \InvalidArgumentException('条件类型不匹配');
        }

        $condition->setMinDays($config['minDays']);
        $condition->setMaxDays($config['maxDays'] ?? null);
    }

    public function checkRequirement(RequirementInterface $requirement, UserInterface $user, Coupon $coupon): bool
    {
        if (!$requirement instanceof RegisterDaysRequirement) {
            return false;
        }

        // 通过反射获取用户创建时间
        $createTime = $this->getUserCreateTime($user);

        if (!$createTime) {
            throw new CouponRequirementException('用户注册时间不存在');
        }

        $registerDays = Carbon::now()->diff($createTime)->days;

        if ($registerDays < $requirement->getMinDays()) {
            throw new CouponRequirementException("需要注册满{$requirement->getMinDays()}天才能领取");
        }

        if ($requirement->getMaxDays() && $registerDays > $requirement->getMaxDays()) {
            throw new CouponRequirementException("注册时间超过{$requirement->getMaxDays()}天无法领取");
        }

        return true;
    }

    public function getDisplayText(ConditionInterface $condition): string
    {
        if (!$condition instanceof RegisterDaysRequirement) {
            return '';
        }

        $text = "注册满{$condition->getMinDays()}天";
        if ($condition->getMaxDays()) {
            $text .= "且不超过{$condition->getMaxDays()}天";
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
     * 获取用户创建时间
     */
    private function getUserCreateTime(UserInterface $user): ?\DateTimeInterface
    {
        // 尝试通过反射获取创建时间
        $reflection = new \ReflectionClass($user);
        
        // 常见的方法名
        $methods = ['getCreateTime', 'getCreatedAt', 'getCreatedTime', 'getDateCreated'];
        
        foreach ($methods as $method) {
            if ($reflection->hasMethod($method)) {
                try {
                    $createTime = $user->$method();
                    if ($createTime instanceof \DateTimeInterface) {
                        return $createTime;
                    }
                } catch (\Throwable) {
                    continue;
                }
            }
        }

        return null;
    }
}
