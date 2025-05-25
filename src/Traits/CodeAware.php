<?php

namespace Tourze\CouponCoreBundle\Traits;

use Tourze\CouponCoreBundle\Entity\Code;

trait CodeAware
{
    private ?Code $code = null;

    public function getCode(): ?Code
    {
        return $this->code;
    }

    public function setCode(?Code $code): void
    {
        $this->code = $code;
    }
}
