<?php

namespace Tourze\CouponCoreBundle\Service;

use Knp\Menu\ItemInterface;
use Tourze\CouponCoreBundle\Entity\Category;
use Tourze\CouponCoreBundle\Entity\Channel;
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
        if (!$item->getChild('优惠券管理')) {
            $item->addChild('优惠券管理');
        }

        $couponMenu = $item->getChild('优惠券管理');

        // 优惠券菜单
        $couponMenu->addChild('优惠券管理')
            ->setUri($this->linkGenerator->getCurdListPage(Coupon::class))
            ->setAttribute('icon', 'fas fa-ticket-alt');

        // 分类管理菜单
        $couponMenu->addChild('分类管理')
            ->setUri($this->linkGenerator->getCurdListPage(Category::class))
            ->setAttribute('icon', 'fas fa-tags');

        // // 券码管理菜单
        // $couponMenu->addChild('券码管理')
        //     ->setUri($this->linkGenerator->getCurdListPage(Code::class))
        //     ->setAttribute('icon', 'fas fa-qrcode');

        // 渠道管理菜单
        $couponMenu->addChild('渠道管理')
            ->setUri($this->linkGenerator->getCurdListPage(Channel::class))
            ->setAttribute('icon', 'fas fa-broadcast-tower');

        // // 条件管理菜单
        // $couponMenu->addChild('条件管理')
        //     ->setUri($this->linkGenerator->getCurdListPage(BaseCondition::class))
        //     ->setAttribute('icon', 'fas fa-filter');

        // // 领取条件菜单
        // $couponMenu->addChild('领取条件')
        //     ->setUri($this->linkGenerator->getCurdListPage(Requirement::class))
        //     ->setAttribute('icon', 'fas fa-hand-paper');

        // // 使用条件菜单
        // $couponMenu->addChild('使用条件')
        //     ->setUri($this->linkGenerator->getCurdListPage(Satisfy::class))
        //     ->setAttribute('icon', 'fas fa-check-circle');

        // // 优惠信息菜单
        // $couponMenu->addChild('优惠信息')
        //     ->setUri($this->linkGenerator->getCurdListPage(Discount::class))
        //     ->setAttribute('icon', 'fas fa-percentage');

        // // 批次管理菜单
        // $couponMenu->addChild('批次管理')
        //     ->setUri($this->linkGenerator->getCurdListPage(Batch::class))
        //     ->setAttribute('icon', 'fas fa-layer-group');
    }
}
