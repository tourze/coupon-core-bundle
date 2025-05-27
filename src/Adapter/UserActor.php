<?php

namespace Tourze\CouponCoreBundle\Adapter;

use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\ConditionSystemBundle\Interface\ActorInterface;

/**
 * 用户执行者适配器
 */
class UserActor implements ActorInterface
{
    public function __construct(
        private readonly UserInterface $user
    ) {}

    public function getActorId(): string
    {
        return $this->user->getUserIdentifier();
    }

    public function getActorType(): string
    {
        return 'user';
    }

    public function getActorData(): array
    {
        return [
            'identifier' => $this->user->getUserIdentifier(),
            'roles' => $this->user->getRoles(),
        ];
    }

    /**
     * 获取原始用户实体
     */
    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
