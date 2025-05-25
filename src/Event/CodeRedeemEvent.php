<?php

namespace Tourze\CouponCoreBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Tourze\CouponCoreBundle\Traits\CodeAware;

class CodeRedeemEvent extends Event
{
    use CodeAware;

    private ?object $extra = null;

    public function getExtra(): ?object
    {
        return $this->extra;
    }

    public function setExtra(?object $extra): void
    {
        $this->extra = $extra;
    }
}
