<?php

namespace Tourze\CouponCoreBundle\ValueObject;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * 条件上下文
 */
class ConditionContext
{
    private function __construct(
        private readonly UserInterface $user,
        private readonly ?object $data = null
    ) {}

    public static function forRequirement(UserInterface $user): self
    {
        return new self($user);
    }

    public static function forSatisfy(UserInterface $user, object $data): self
    {
        return new self($user, $data);
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getData(): ?object
    {
        return $this->data;
    }
}
