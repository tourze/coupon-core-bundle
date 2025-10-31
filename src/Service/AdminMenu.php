<?php

namespace Tourze\CouponCoreBundle\Service;

use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\CouponCoreBundle\Entity\Batch;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Entity\CouponStat;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

/**
 * 优惠券管理菜单服务
 */
#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('优惠券管理')) {
            $item->addChild('优惠券管理');
        }

        $couponMenu = $item->getChild('优惠券管理');
        if (null === $couponMenu) {
            return;
        }

        $couponMenu->addChild('优惠券管理')
            ->setUri($this->linkGenerator->getCurdListPage(Coupon::class))
            ->setAttribute('icon', 'fas fa-ticket-alt')
        ;
        $couponMenu->addChild('券码管理')
            ->setUri($this->linkGenerator->getCurdListPage(Code::class))
            ->setAttribute('icon', 'fas fa-qrcode')
        ;
        $couponMenu->addChild('批次管理')
            ->setUri($this->linkGenerator->getCurdListPage(Batch::class))
            ->setAttribute('icon', 'fas fa-layer-group')
        ;
        $couponMenu->addChild('统计数据')
            ->setUri($this->linkGenerator->getCurdListPage(CouponStat::class))
            ->setAttribute('icon', 'fas fa-chart-bar')
        ;
    }
}
