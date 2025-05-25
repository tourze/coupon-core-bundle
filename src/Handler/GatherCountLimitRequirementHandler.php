<?php

namespace Tourze\CouponCoreBundle\Handler;

use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Entity\GatherCountLimitRequirement;
use Tourze\CouponCoreBundle\Enum\ConditionScenario;
use Tourze\CouponCoreBundle\Exception\CouponRequirementException;
use Tourze\CouponCoreBundle\Interface\ConditionInterface;
use Tourze\CouponCoreBundle\Interface\RequirementHandlerInterface;
use Tourze\CouponCoreBundle\Interface\RequirementInterface;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\CouponCoreBundle\ValueObject\ConditionContext;
use Tourze\CouponCoreBundle\ValueObject\FormFieldFactory;
use Tourze\CouponCoreBundle\ValueObject\ValidationResult;

/**
 * 领取次数限制条件处理器
 */
class GatherCountLimitRequirementHandler implements RequirementHandlerInterface
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

    public function createCondition(Coupon $coupon, array $config): ConditionInterface
    {
        $requirement = new GatherCountLimitRequirement();
        $requirement->setCoupon($coupon);
        $requirement->setType($this->getType());
        $requirement->setLabel($this->getLabel());
        $requirement->setMaxCount($config['maxCount']);

        return $requirement;
    }

    public function updateCondition(ConditionInterface $condition, array $config): void
    {
        if (!$condition instanceof GatherCountLimitRequirement) {
            throw new \InvalidArgumentException('条件类型不匹配');
        }

        $condition->setMaxCount($config['maxCount']);
    }

    public function checkRequirement(RequirementInterface $requirement, UserInterface $user, Coupon $coupon): bool
    {
        if (!$requirement instanceof GatherCountLimitRequirement) {
            return false;
        }

        // 查询用户已领取该优惠券的次数
        $gatherCount = $this->codeRepository->count([
            'coupon' => $coupon,
            'owner' => $user,
        ]);

        if ($gatherCount >= $requirement->getMaxCount()) {
            throw new CouponRequirementException('已达到领取上限');
        }

        return true;
    }

    public function getDisplayText(ConditionInterface $condition): string
    {
        if (!$condition instanceof GatherCountLimitRequirement) {
            return '';
        }

        return "最多可领取{$condition->getMaxCount()}次";
    }

    public function getSupportedScenarios(): array
    {
        return [ConditionScenario::REQUIREMENT];
    }

    public function validate(ConditionInterface $condition, ConditionContext $context): ValidationResult
    {
        return ValidationResult::success();
    }
}
