<?php

namespace Tourze\CouponCoreBundle\Message;

use Tourze\AsyncContracts\AsyncMessageInterface;

class CreateCodeMessage implements AsyncMessageInterface
{
    /**
     * @var int 优惠券ID
     */
    private int $couponId;

    /**
     * @var int 数量
     */
    private int $quantity;

    public function getCouponId(): int
    {
        return $this->couponId;
    }

    public function setCouponId(int $couponId): void
    {
        $this->couponId = $couponId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }
}
