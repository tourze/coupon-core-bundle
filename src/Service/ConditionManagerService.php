<?php

namespace Tourze\CouponCoreBundle\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Enum\ConditionScenario;
use Tourze\CouponCoreBundle\Exception\CouponRequirementException;
use Tourze\CouponCoreBundle\Exception\InvalidConditionConfigException;
use Tourze\CouponCoreBundle\Interface\ConditionInterface;
use Tourze\CouponCoreBundle\Interface\RequirementHandlerInterface;
use Tourze\CouponCoreBundle\Interface\RequirementInterface;
use Tourze\CouponCoreBundle\Interface\SatisfyHandlerInterface;
use Tourze\CouponCoreBundle\Interface\SatisfyInterface;
use Tourze\CouponCoreBundle\ValueObject\OrderContext;
use Tourze\CouponCoreBundle\ValueObject\ValidationResult;

/**
 * 条件管理服务
 */
class ConditionManagerService
{
    public function __construct(
        private readonly ConditionHandlerFactory $handlerFactory,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * 创建条件
     */
    public function createCondition(Coupon $coupon, string $type, array $config): ConditionInterface
    {
        $handler = $this->handlerFactory->getHandler($type);
        
        // 验证配置
        $validationResult = $handler->validateConfig($config);
        if (!$validationResult->isValid()) {
            throw new InvalidConditionConfigException(
                '条件配置无效: ' . implode(', ', $validationResult->getErrors())
            );
        }

        $condition = $handler->createCondition($coupon, $config);
        
        $this->entityManager->persist($condition);
        
        return $condition;
    }

    /**
     * 更新条件
     */
    public function updateCondition(ConditionInterface $condition, array $config): void
    {
        $handler = $this->handlerFactory->getHandler($condition->getType());
        
        // 验证配置
        $validationResult = $handler->validateConfig($config);
        if (!$validationResult->isValid()) {
            throw new InvalidConditionConfigException(
                '条件配置无效: ' . implode(', ', $validationResult->getErrors())
            );
        }

        $handler->updateCondition($condition, $config);
    }

    /**
     * 删除条件
     */
    public function deleteCondition(ConditionInterface $condition): void
    {
        $this->entityManager->remove($condition);
    }

    /**
     * 验证领取条件
     */
    public function checkRequirements(Coupon $coupon, UserInterface $user): bool
    {
        $requirements = $this->getRequirements($coupon);

        foreach ($requirements as $requirement) {
            if (!$requirement->isEnabled()) {
                continue;
            }

            $handler = $this->handlerFactory->getHandler($requirement->getType());
            if (!$handler instanceof RequirementHandlerInterface) {
                continue;
            }

            try {
                if (!$handler->checkRequirement($requirement, $user, $coupon)) {
                    return false;
                }
            } catch (CouponRequirementException $e) {
                $this->logger->error('发生CouponRequirementException异常', [
                    'exception' => $e,
                ]);
                // 重新抛出异常，保持原有的异常处理逻辑
                throw $e;
            }
        }

        return true;
    }

    /**
     * 验证使用条件
     */
    public function checkSatisfies(Coupon $coupon, OrderContext $orderContext): bool
    {
        $satisfies = $this->getSatisfies($coupon);
        
        foreach ($satisfies as $satisfy) {
            if (!$satisfy->isEnabled()) {
                continue;
            }

            $handler = $this->handlerFactory->getHandler($satisfy->getType());
            
            if (!$handler instanceof SatisfyHandlerInterface) {
                continue;
            }

            if (!$handler->checkSatisfy($satisfy, $orderContext)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 获取条件的显示文本
     */
    public function getConditionDisplayText(ConditionInterface $condition): string
    {
        $handler = $this->handlerFactory->getHandler($condition->getType());
        return $handler->getDisplayText($condition);
    }

    /**
     * 获取可用的条件类型
     */
    public function getAvailableConditionTypes(ConditionScenario $scenario): array
    {
        $types = [];

        foreach ($this->handlerFactory->getAllHandlers() as $handler) {
            if (in_array($scenario, $handler->getSupportedScenarios(), true)) {
                $formFields = [];
                foreach ($handler->getFormFields() as $field) {
                    $formFields[] = $field->toArray();
                }

                $types[] = [
                    'type' => $handler->getType(),
                    'label' => $handler->getLabel(),
                    'description' => $handler->getDescription(),
                    'formFields' => $formFields,
                ];
            }
        }

        return $types;
    }

    /**
     * 验证条件配置
     */
    public function validateConditionConfig(string $type, array $config): ValidationResult
    {
        $handler = $this->handlerFactory->getHandler($type);
        return $handler->validateConfig($config);
    }

    /**
     * 获取优惠券的领取条件
     * 
     * @return Collection<RequirementInterface>
     */
    private function getRequirements(Coupon $coupon): Collection
    {
        return $coupon->getRequirementConditions();
    }

    /**
     * 获取优惠券的使用条件
     * 
     * @return Collection<SatisfyInterface>
     */
    private function getSatisfies(Coupon $coupon): Collection
    {
        return $coupon->getSatisfyConditions();
    }
}
