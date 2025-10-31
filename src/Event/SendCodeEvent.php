<?php

namespace Tourze\CouponCoreBundle\Event;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Tourze\CouponCoreBundle\Traits\CodeAware;
use Tourze\CouponCoreBundle\Traits\CouponAware;

class SendCodeEvent extends Event
{
    use CouponAware;
    use CodeAware;

    private ?UserInterface $user = null;

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): void
    {
        $this->user = $user;
    }

    public ?string $extend = '';

    public function getExtend(): ?string
    {
        return $this->extend;
    }

    public function setExtend(?string $extend): void
    {
        $this->extend = $extend;
    }
}
