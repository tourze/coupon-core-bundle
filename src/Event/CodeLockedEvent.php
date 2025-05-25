<?php

namespace Tourze\CouponCoreBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Tourze\CouponCoreBundle\Traits\CodeAware;

class CodeLockedEvent extends Event
{
    use CodeAware;
}
