<?php

namespace Tourze\CouponCoreBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\CouponCoreBundle\Entity\Coupon;

/**
 * 优惠券管理控制器
 */
class CouponCrudController extends AbstractCrudController
{
    public function __construct(
    ) {}

    public static function getEntityFqcn(): string
    {
        return Coupon::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('优惠券')
            ->setEntityLabelInPlural('优惠券管理')
            ->setPageTitle('index', '优惠券列表')
            ->setPageTitle('new', '新建优惠券')
            ->setPageTitle('edit', '编辑优惠券')
            ->setPageTitle('detail', '优惠券详情')
            ->setHelp('index', '管理系统中的所有优惠券，包括券码、条件、优惠信息等')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'name', 'sn', 'remark'])
            ->setFormThemes(['@CouponCore/admin/coupon/form_theme.html.twig', '@EasyAdmin/crud/form_theme.html.twig']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, Action::EDIT, Action::DELETE])
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('新建优惠券');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setLabel('编辑');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setLabel('删除');
            })
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setLabel('查看详情');
            });
    }

    public function configureFields(string $pageName): iterable
    {
        // 基础信息Tab
        yield FormField::addTab('基础信息')->setIcon('fa fa-info-circle');

        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
            ->setMaxLength(9999);

        yield TextField::new('sn', '唯一编码')
            ->setRequired(false)
            ->hideOnForm()
            ->setHelp('系统自动生成的唯一编码');

        yield TextField::new('name', '优惠券名称')
            ->setRequired(true)
            ->setMaxLength(255)
            ->setColumns(12);

        yield AssociationField::new('category', '分类')
            ->setRequired(false)
            ->autocomplete()
            ->setColumns(6);

        yield BooleanField::new('valid', '有效状态')
            ->setRequired(false)
            ->setColumns(6);

        yield ImageField::new('iconImg', 'ICON图标')
            ->setBasePath('/uploads')
            ->setUploadDir('public/uploads')
            ->setUploadedFileNamePattern('[year]/[month]/[day]/[contenthash].[extension]')
            ->setRequired(false)
            ->setColumns(6)
            ->onlyOnForms();

        yield ImageField::new('backImg', '列表背景')
            ->setBasePath('/uploads')
            ->setUploadDir('public/uploads')
            ->setUploadedFileNamePattern('[year]/[month]/[day]/[contenthash].[extension]')
            ->setRequired(false)
            ->setColumns(6)
            ->onlyOnForms();

        yield TextareaField::new('remark', '备注')
            ->setRequired(false)
            ->setMaxLength(1000)
            ->hideOnIndex()
            ->setColumns(12);

        yield TextareaField::new('useDesc', '使用说明')
            ->setRequired(false)
            ->hideOnIndex()
            ->setColumns(12);

        // 时间设置Tab
        yield FormField::addTab('时间设置')->setIcon('fa fa-clock')->onlyOnForms();

        yield IntegerField::new('expireDay', '领取后过期天数')
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('用户领取后多少天过期，为空则不限制');

        yield DateTimeField::new('startTime', '开始有效时间')
            ->setRequired(false)
            ->setColumns(6);

        yield DateTimeField::new('endTime', '截止有效时间')
            ->setRequired(false)
            ->setColumns(6);

        yield DateTimeField::new('startDateTime', '可用开始时间')
            ->setRequired(false)
            ->setColumns(6);

        yield DateTimeField::new('endDateTime', '可用结束时间')
            ->setRequired(false)
            ->setColumns(6);

        yield BooleanField::new('needActive', '是否需要激活')
            ->setRequired(false)
            ->setColumns(6);

        yield IntegerField::new('activeValidDay', '激活后有效天数')
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('激活后多少天内有效');

        // 其他设置Tab
        yield FormField::addTab('其他设置')->setIcon('fa fa-cogs')->onlyOnForms();

        yield AssociationField::new('channels', '投放渠道')
            ->setRequired(false)
            ->autocomplete()
            ->setFormTypeOption('multiple', true)
            ->setColumns(12);

        // 统计信息 (仅列表和详情)
        if ($pageName !== Crud::PAGE_NEW && $pageName !== Crud::PAGE_EDIT) {
            yield IntegerField::new('renderCodeCount', '券码数量')
                ->onlyOnIndex()
                ->formatValue(function ($value) {
                    return number_format($value);
                });

            // 审计字段
            yield TextField::new('createdBy', '创建人')
                ->onlyOnDetail();

            yield TextField::new('updatedBy', '更新人')
                ->onlyOnDetail();

            yield DateTimeField::new('createTime', '创建时间')
                ->onlyOnDetail();

            yield DateTimeField::new('updateTime', '更新时间')
                ->onlyOnDetail();
        }
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', '优惠券名称'))
            ->add(TextFilter::new('sn', '唯一编码'))
            ->add(EntityFilter::new('category', '分类'))
            ->add(BooleanFilter::new('valid', '有效状态'))
            ->add(BooleanFilter::new('needActive', '需要激活'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('updateTime', '更新时间'))
            ->add(DateTimeFilter::new('startTime', '开始有效时间'))
            ->add(DateTimeFilter::new('endTime', '截止有效时间'));
    }
}
