<?php

namespace Tourze\CouponCoreBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Enum\ConditionScenario;
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
        private readonly EntityManagerInterface $entityManager
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

            if (!$handler->checkRequirement($requirement, $user, $coupon)) {
                return false;
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
     * @return RequirementInterface[]
     */
    private function getRequirements(Coupon $coupon): array
    {
        // 这里需要根据实际的关联关系来获取条件
        // 由于新的设计使用了JOINED继承，需要查询所有的BaseCondition
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('c')
           ->from('Tourze\CouponCoreBundle\Entity\BaseCondition', 'c')
           ->where('c.coupon = :coupon')
           ->andWhere('c INSTANCE OF Tourze\CouponCoreBundle\Interface\RequirementInterface')
           ->setParameter('coupon', $coupon);

        return $qb->getQuery()->getResult();
    }

    /**
     * 获取优惠券的使用条件
     * 
     * @return SatisfyInterface[]
     */
    private function getSatisfies(Coupon $coupon): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('c')
           ->from('Tourze\CouponCoreBundle\Entity\BaseCondition', 'c')
           ->where('c.coupon = :coupon')
           ->andWhere('c INSTANCE OF Tourze\CouponCoreBundle\Interface\SatisfyInterface')
           ->setParameter('coupon', $coupon);

        return $qb->getQuery()->getResult();
    }
}
