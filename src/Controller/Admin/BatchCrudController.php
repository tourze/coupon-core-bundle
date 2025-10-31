<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\CouponCoreBundle\Entity\Batch;

/**
 * 批次管理控制器
 * @extends AbstractCrudController<Batch>
 */
#[AdminCrud(routePath: '/coupon/batch', routeName: 'coupon_batch')]
final class BatchCrudController extends AbstractCrudController
{
    public function __construct(
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Batch::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('批次')
            ->setEntityLabelInPlural('批次管理')
            ->setPageTitle('index', '批次列表')
            ->setPageTitle('new', '新建批次')
            ->setPageTitle('edit', '编辑批次')
            ->setPageTitle('detail', '批次详情')
            ->setHelp('index', '管理优惠券的发放批次，跟踪券码的发送进度')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'remark'])
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
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

        yield AssociationField::new('coupon', '关联优惠券')
            ->setRequired(true)
            ->setColumns(12)
            ->setHelp('选择此批次所属的优惠券')
        ;

        yield IntegerField::new('totalNum', '总数量')
            ->setRequired(true)
            ->setColumns(6)
            ->setHelp('此批次计划发送的券码总数量')
        ;

        yield IntegerField::new('sendNum', '已发送数量')
            ->setRequired(true)
            ->setColumns(6)
            ->setHelp('此批次已经发送出去的券码数量')
        ;

        yield TextareaField::new('remark', '备注')
            ->setRequired(false)
            ->setMaxLength(65535)
            ->hideOnIndex()
            ->setColumns(12)
            ->setHelp('批次的备注信息')
        ;

        // 审计字段 (仅显示，不可编辑)
        if (Crud::PAGE_NEW !== $pageName && Crud::PAGE_EDIT !== $pageName) {
            yield TextField::new('createdBy', '创建人')
                ->onlyOnDetail()
            ;

            yield TextField::new('updatedBy', '更新人')
                ->onlyOnDetail()
            ;

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
            ->add(EntityFilter::new('coupon', '关联优惠券'))
            ->add(NumericFilter::new('totalNum', '总数量'))
            ->add(NumericFilter::new('sendNum', '已发送数量'))
            ->add(TextFilter::new('remark', '备注'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('updateTime', '更新时间'))
            ->add(TextFilter::new('createdBy', '创建人'))
            ->add(TextFilter::new('updatedBy', '更新人'))
        ;
    }
}
