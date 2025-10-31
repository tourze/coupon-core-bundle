<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\CouponCoreBundle\Entity\CouponStat;

/**
 * 优惠券统计管理控制器
 * @extends AbstractCrudController<CouponStat>
 */
#[AdminCrud(routePath: '/coupon/couponstat', routeName: 'coupon_couponstat')]
final class CouponStatCrudController extends AbstractCrudController
{
    public function __construct(
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return CouponStat::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('优惠券统计')
            ->setEntityLabelInPlural('优惠券统计管理')
            ->setPageTitle('index', '优惠券统计列表')
            ->setPageTitle('new', '新建优惠券统计')
            ->setPageTitle('edit', '编辑优惠券统计')
            ->setPageTitle('detail', '优惠券统计详情')
            ->setHelp('index', '查看优惠券的统计数据，包括总数、已领取、已使用和已过期数量')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'couponId'])
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setLabel('查看详情');
            })
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
            ->setMaxLength(9999)
        ;

        yield TextField::new('couponId', '优惠券ID')
            ->setRequired(false)
            ->setColumns(12)
            ->setHelp('关联的优惠券ID')
        ;

        yield IntegerField::new('totalNum', '总数量')
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('优惠券券码的总数量')
            ->formatValue(function ($value) {
                return number_format(is_numeric($value) ? (float) $value : 0.0);
            })
        ;

        yield IntegerField::new('receivedNum', '已领取数量')
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('用户已领取的券码数量')
            ->formatValue(function ($value) {
                return number_format(is_numeric($value) ? (float) $value : 0.0);
            })
        ;

        yield IntegerField::new('usedNum', '已使用数量')
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('用户已使用的券码数量')
            ->formatValue(function ($value) {
                return number_format(is_numeric($value) ? (float) $value : 0.0);
            })
        ;

        yield IntegerField::new('expiredNum', '已过期数量')
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('已过期的券码数量')
            ->formatValue(function ($value) {
                return number_format(is_numeric($value) ? (float) $value : 0.0);
            })
        ;

        // 审计字段 (仅显示，不可编辑)
        if (Crud::PAGE_NEW !== $pageName && Crud::PAGE_EDIT !== $pageName) {
            yield DateTimeField::new('createTime', '创建时间')
                ->onlyOnDetail()
            ;

            yield DateTimeField::new('updateTime', '更新时间')
                ->onlyOnDetail()
            ;
        }
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('couponId', '优惠券ID'))
            ->add(NumericFilter::new('totalNum', '总数量'))
            ->add(NumericFilter::new('receivedNum', '已领取数量'))
            ->add(NumericFilter::new('usedNum', '已使用数量'))
            ->add(NumericFilter::new('expiredNum', '已过期数量'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('updateTime', '更新时间'))
        ;
    }
}
