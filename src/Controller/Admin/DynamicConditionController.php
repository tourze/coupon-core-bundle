<?php

namespace Tourze\CouponCoreBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Tourze\CouponCoreBundle\Enum\ConditionScenario;
use Tourze\CouponCoreBundle\Service\ConditionHandlerFactory;
use Tourze\CouponCoreBundle\Service\ConditionManagerService;

/**
 * 动态条件管理控制器
 */
#[Route('/admin/condition', name: 'admin_condition_')]
class DynamicConditionController extends AbstractController
{
    public function __construct(
        private readonly ConditionHandlerFactory $handlerFactory,
        private readonly ConditionManagerService $conditionManager
    ) {}

    /**
     * 获取条件类型列表
     */
    #[Route('/types/{scenario}', name: 'types', methods: ['GET'])]
    public function getConditionTypes(string $scenario): JsonResponse
    {
        try {
            $conditionScenario = ConditionScenario::from($scenario);
            $types = $this->conditionManager->getAvailableConditionTypes($conditionScenario);
            
            return new JsonResponse([
                'success' => true,
                'data' => $types,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * 获取条件类型的表单字段配置
     */
    #[Route('/form-fields/{type}', name: 'form_fields', methods: ['GET'])]
    public function getFormFields(string $type): JsonResponse
    {
        try {
            $handler = $this->handlerFactory->getHandler($type);

            $formFields = [];
            foreach ($handler->getFormFields() as $field) {
                $formFields[] = $field->toArray();
            }

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'type' => $type,
                    'label' => $handler->getLabel(),
                    'description' => $handler->getDescription(),
                    'formFields' => $formFields,
                ],
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * 验证条件配置
     */
    #[Route('/validate', name: 'validate', methods: ['POST'])]
    public function validateConfig(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $type = $data['type'] ?? '';
            $config = $data['config'] ?? [];

            $validationResult = $this->conditionManager->validateConditionConfig($type, $config);
            
            return new JsonResponse([
                'success' => $validationResult->isValid(),
                'errors' => $validationResult->getErrors(),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * 条件管理页面
     */
    #[Route('/manage', name: 'manage')]
    public function manage(): Response
    {
        $requirementTypes = $this->conditionManager->getAvailableConditionTypes(ConditionScenario::REQUIREMENT);
        $satisfyTypes = $this->conditionManager->getAvailableConditionTypes(ConditionScenario::SATISFY);

        return $this->render('@CouponCore/admin/condition_manage.html.twig', [
            'requirementTypes' => $requirementTypes,
            'satisfyTypes' => $satisfyTypes,
        ]);
    }
}
