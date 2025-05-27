<?php

namespace Tourze\CouponCoreBundle\Handler;

use Carbon\Carbon;
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
use Tourze\CouponCoreBundle\Entity\RegisterDaysCondition;

/**
 * 注册天数条件处理器
 */
class RegisterDaysConditionHandler extends AbstractConditionHandler
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

    public function createCondition(SubjectInterface $subject, array $config): ConditionInterface
    {
        if (!$subject instanceof CouponSubject) {
            throw new \InvalidArgumentException('主体必须是优惠券类型');
        }

        $condition = new RegisterDaysCondition();
        $condition->setCoupon($subject->getCoupon());
        $condition->setType($this->getType());
        $condition->setLabel($this->getLabel());
        $condition->setMinDays($config['minDays']);

        if (isset($config['maxDays'])) {
            $condition->setMaxDays($config['maxDays']);
        }

        return $condition;
    }

    public function updateCondition(ConditionInterface $condition, array $config): void
    {
        if (!$condition instanceof RegisterDaysCondition) {
            throw new \InvalidArgumentException('条件类型不匹配');
        }

        $condition->setMinDays($config['minDays']);
        $condition->setMaxDays($config['maxDays'] ?? null);
    }

    protected function doEvaluate(ConditionInterface $condition, EvaluationContext $context): EvaluationResult
    {
        if (!$condition instanceof RegisterDaysCondition) {
            return EvaluationResult::fail(['条件类型不匹配']);
        }

        $actor = $context->getActor();
        if (!$actor instanceof UserActor) {
            return EvaluationResult::fail(['执行者必须是用户类型']);
        }

        $user = $actor->getUser();
        $createTime = $this->getUserCreateTime($user);

        if (!$createTime) {
            return EvaluationResult::fail(['用户注册时间不存在']);
        }

        $registerDays = Carbon::now()->diff($createTime)->days;

        if ($registerDays < $condition->getMinDays()) {
            return EvaluationResult::fail([
                "需要注册满{$condition->getMinDays()}天才能领取"
            ]);
        }

        if ($condition->getMaxDays() && $registerDays > $condition->getMaxDays()) {
            return EvaluationResult::fail([
                "注册时间超过{$condition->getMaxDays()}天无法领取"
            ]);
        }

        return EvaluationResult::pass([
            'register_days' => $registerDays,
            'min_days' => $condition->getMinDays(),
            'max_days' => $condition->getMaxDays(),
        ]);
    }

    public function getDisplayText(ConditionInterface $condition): string
    {
        if (!$condition instanceof RegisterDaysCondition) {
            return '';
        }

        $text = "注册满{$condition->getMinDays()}天";
        if ($condition->getMaxDays()) {
            $text .= "且不超过{$condition->getMaxDays()}天";
        }

        return $text;
    }

    public function getSupportedTriggers(): array
    {
        return [ConditionTrigger::BEFORE_ACTION];
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