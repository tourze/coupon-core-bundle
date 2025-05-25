<?php

namespace Tourze\CouponCoreBundle\Event;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Tourze\CouponCoreBundle\Traits\CodeAware;

class CodeNotFoundEvent extends Event
{
    use CodeAware;

    /**
     * @var string SN编码
     */
    private string $sn;

    /**
     * @var UserInterface 要查找的用户
     */
    private UserInterface $user;

    public function getSn(): string
    {
        return $this->sn;
    }

    public function setSn(string $sn): void
    {
        $this->sn = $sn;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): void
    {
        $this->user = $user;
    }
}
