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
use Tourze\CouponCoreBundle\Adapter\UserActor;
use Tourze\CouponCoreBundle\Entity\GatherCountLimitCondition;
use Tourze\CouponCoreBundle\Repository\CodeRepository;

/**
 * 领取次数限制条件处理器
 */
class GatherCountLimitConditionHandler extends AbstractConditionHandler
{
    public function __construct(
        private readonly CodeRepository $codeRepository
    ) {}

    public function getType(): string
    {
        return 'gather_count_limit';
    }

    public function getLabel(): string
    {
        return '领取次数限制';
    }

    public function getDescription(): string
    {
        return '限制用户对该优惠券的领取次数';
    }

    public function getFormFields(): iterable
    {
        yield FormFieldFactory::integer('maxCount', '最大领取次数')
            ->required()
            ->min(1)
            ->help('用户最多可以领取多少次该优惠券');
    }

    public function validateConfig(array $config): ValidationResult
    {
        $errors = [];

        if (!isset($config['maxCount']) || !is_int($config['maxCount']) || $config['maxCount'] <= 0) {
            $errors[] = '最大领取次数必须是正整数';
        }

        return empty($errors) ? ValidationResult::success() : ValidationResult::failure($errors);
    }

    public function createCondition(SubjectInterface $subject, array $config): ConditionInterface
    {
        if (!$subject instanceof CouponSubject) {
            throw new \InvalidArgumentException('主体必须是优惠券类型');
        }

        $condition = new GatherCountLimitCondition();
        $condition->setCoupon($subject->getCoupon());
        $condition->setType($this->getType());
        $condition->setLabel($this->getLabel());
        $condition->setMaxCount($config['maxCount']);

        return $condition;
    }

    public function updateCondition(ConditionInterface $condition, array $config): void
    {
        if (!$condition instanceof GatherCountLimitCondition) {
            throw new \InvalidArgumentException('条件类型不匹配');
        }

        $condition->setMaxCount($config['maxCount']);
    }

    protected function doEvaluate(ConditionInterface $condition, EvaluationContext $context): EvaluationResult
    {
        if (!$condition instanceof GatherCountLimitCondition) {
            return EvaluationResult::fail(['条件类型不匹配']);
        }

        $actor = $context->getActor();
        if (!$actor instanceof UserActor) {
            return EvaluationResult::fail(['执行者必须是用户类型']);
        }

        $user = $actor->getUser();
        $coupon = $condition->getCoupon();

        // 查询用户已领取该优惠券的次数
        $gatherCount = $this->codeRepository->count([
            'coupon' => $coupon,
            'owner' => $user,
        ]);

        if ($gatherCount >= $condition->getMaxCount()) {
            return EvaluationResult::fail(['已达到领取上限']);
        }

        return EvaluationResult::pass([
            'current_count' => $gatherCount,
            'max_count' => $condition->getMaxCount(),
            'remaining_count' => $condition->getMaxCount() - $gatherCount,
        ]);
    }

    public function getDisplayText(ConditionInterface $condition): string
    {
        if (!$condition instanceof GatherCountLimitCondition) {
            return '';
        }

        return "最多可领取{$condition->getMaxCount()}次";
    }

    public function getSupportedTriggers(): array
    {
        return [ConditionTrigger::BEFORE_ACTION];
    }
} 