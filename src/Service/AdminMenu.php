<?php

namespace Tourze\CouponCoreBundle\Service;

use Knp\Menu\ItemInterface;
use Tourze\CouponCoreBundle\Entity\Category;
use Tourze\CouponCoreBundle\Entity\Channel;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

/**
 * 优惠券管理菜单服务
 */
class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private readonly LinkGeneratorInterface $linkGenerator,
    )
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        if ($item->getChild('优惠券管理') === null) {
            $item->addChild('优惠券管理');
        }

        $couponMenu = $item->getChild('优惠券管理');

        $couponMenu->addChild('优惠券管理')
            ->setUri($this->linkGenerator->getCurdListPage(Coupon::class))
            ->setAttribute('icon', 'fas fa-ticket-alt');
        $couponMenu->addChild('券码管理')
            ->setUri($this->linkGenerator->getCurdListPage(Code::class))
            ->setAttribute('icon', 'fas fa-qrcode');
        $couponMenu->addChild('分类管理')
            ->setUri($this->linkGenerator->getCurdListPage(Category::class))
            ->setAttribute('icon', 'fas fa-tags');
        $couponMenu->addChild('渠道管理')
            ->setUri($this->linkGenerator->getCurdListPage(Channel::class))
            ->setAttribute('icon', 'fas fa-broadcast-tower');
    }
}
