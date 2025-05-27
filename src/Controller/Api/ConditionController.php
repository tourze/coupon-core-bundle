<?php

namespace Tourze\CouponCoreBundle\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Tourze\ConditionSystemBundle\Service\ConditionManagerService;
use Tourze\CouponCoreBundle\Entity\Coupon;

/**
 * 条件API控制器
 */
#[Route('/api/coupon/condition', name: 'api_coupon_condition_')]
class ConditionController extends AbstractController
{
    public function __construct(
        private readonly ConditionManagerService $conditionManager,
        private readonly EntityManagerInterface $entityManager
    ) {}

    /**
     * 创建条件
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            $couponId = $data['couponId'] ?? null;
            $type = $data['type'] ?? '';
            $config = $data['config'] ?? [];

            if (!$couponId) {
                return new JsonResponse(['success' => false, 'message' => '优惠券ID不能为空'], 400);
            }

            $coupon = $this->entityManager->getRepository(Coupon::class)->find($couponId);
            if (!$coupon) {
                return new JsonResponse(['success' => false, 'message' => '优惠券不存在'], 404);
            }

            $condition = $this->conditionManager->createCondition($coupon, $type, $config);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'data' => $condition->toArray(),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * 获取条件详情
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            $condition = $this->entityManager->getRepository('Tourze\ConditionSystemBundle\Entity\BaseCondition')->find($id);
            if (!$condition) {
                return new JsonResponse(['success' => false, 'message' => '条件不存在'], 404);
            }

            return new JsonResponse([
                'success' => true,
                'data' => $condition->toArray(),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * 更新条件
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $condition = $this->entityManager->getRepository('Tourze\ConditionSystemBundle\Entity\BaseCondition')->find($id);
            if (!$condition) {
                return new JsonResponse(['success' => false, 'message' => '条件不存在'], 404);
            }

            // 处理基础字段更新
            if (isset($data['enabled'])) {
                $condition->setEnabled($data['enabled']);
            }
            
            if (isset($data['remark'])) {
                $condition->setRemark($data['remark']);
            }

            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'data' => $condition->toArray(),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * 删除条件
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $condition = $this->entityManager->getRepository('Tourze\ConditionSystemBundle\Entity\BaseCondition')->find($id);
            if (!$condition) {
                return new JsonResponse(['success' => false, 'message' => '条件不存在'], 404);
            }

            $this->entityManager->remove($condition);
            $this->entityManager->flush();

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * 获取优惠券的条件列表
     */
    #[Route('/coupon/{couponId}', name: 'list', methods: ['GET'])]
    public function list(int $couponId): JsonResponse
    {
        try {
            $coupon = $this->entityManager->getRepository(Coupon::class)->find($couponId);
            if (!$coupon) {
                return new JsonResponse(['success' => false, 'message' => '优惠券不存在'], 404);
            }

            $conditions = [];
            foreach ($coupon->getConditions() as $condition) {
                $conditions[] = $condition->toArray();
            }

            return new JsonResponse([
                'success' => true,
                'data' => $conditions,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
