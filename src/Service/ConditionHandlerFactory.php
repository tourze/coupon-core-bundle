<?php

namespace Tourze\CouponCoreBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Tourze\CouponCoreBundle\Exception\ConditionHandlerNotFoundException;
use Tourze\CouponCoreBundle\Interface\ConditionHandlerInterface;

/**
 * 条件处理器工厂
 */
class ConditionHandlerFactory
{
    /**
     * @var array<string, ConditionHandlerInterface>
     */
    private array $handlers = [];

    public function __construct(#[TaggedIterator('coupon.condition_handler')] iterable $handlers)
    {
        foreach ($handlers as $handler) {
            assert($handler instanceof ConditionHandlerInterface);
            $this->handlers[$handler->getType()] = $handler;
        }
    }

    /**
     * 获取条件处理器
     */
    public function getHandler(string $type): ConditionHandlerInterface
    {
        if (!isset($this->handlers[$type])) {
            throw new ConditionHandlerNotFoundException("条件处理器 '{$type}' 未找到");
        }

        return $this->handlers[$type];
    }

    /**
     * 获取所有处理器
     * 
     * @return array<string, ConditionHandlerInterface>
     */
    public function getAllHandlers(): array
    {
        return $this->handlers;
    }

    /**
     * 获取所有处理器类型
     * 
     * @return string[]
     */
    public function getAvailableTypes(): array
    {
        return array_keys($this->handlers);
    }

    /**
     * 检查处理器是否存在
     */
    public function hasHandler(string $type): bool
    {
        return isset($this->handlers[$type]);
    }
}
