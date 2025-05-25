<?php

namespace Tourze\CouponCoreBundle\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\CouponCoreBundle\Entity\Channel;

/**
 * 渠道管理控制器
 */
class ChannelCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Channel::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('渠道')
            ->setEntityLabelInPlural('渠道管理')
            ->setPageTitle('index', '渠道列表')
            ->setPageTitle('new', '新建渠道')
            ->setPageTitle('edit', '编辑渠道')
            ->setPageTitle('detail', '渠道详情')
            ->setHelp('index', '管理优惠券投放渠道，包括小程序、H5页面、第三方平台等')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'title', 'code', 'remark', 'appId']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, Action::EDIT, Action::DELETE])
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('新建渠道');
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
        // 基础字段
        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
            ->setMaxLength(9999)
            ->setHelp('系统自动生成的雪花ID');

        yield TextField::new('code', '渠道编码')
            ->setRequired(false)
            ->setMaxLength(100)
            ->setColumns(6)
            ->setHelp('系统自动生成的唯一编码，10位随机字符串');

        yield TextField::new('title', '渠道标题')
            ->setRequired(true)
            ->setMaxLength(60)
            ->setColumns(6)
            ->setHelp('渠道的显示名称');

        // 图片字段
        yield ImageField::new('logo', 'LOGO')
            ->setBasePath('/uploads')
            ->setUploadDir('public/uploads')
            ->setUploadedFileNamePattern('[year]/[month]/[day]/[contenthash].[extension]')
            ->setRequired(false)
            ->setColumns(12)
            ->setHelp('渠道的LOGO图片，建议尺寸：200x200像素');

        // 描述字段
        yield TextareaField::new('remark', '渠道描述')
            ->setRequired(false)
            ->setMaxLength(100)
            ->setColumns(12)
            ->setHelp('渠道的详细描述信息');

        // 链接字段
        yield UrlField::new('redirectUrl', '跳转链接')
            ->setRequired(false)
            ->hideOnIndex()
            ->setColumns(12)
            ->setHelp('用户点击后的跳转地址，支持HTTP/HTTPS协议');

        // 小程序字段
        yield TextField::new('appId', '小程序AppID')
            ->setRequired(false)
            ->setMaxLength(100)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('微信小程序的AppID，用于小程序跳转');

        // 状态字段
        yield BooleanField::new('valid', '有效状态')
            ->setRequired(false)
            ->setColumns(6);

        // 关联信息（仅详情页显示）
        yield AssociationField::new('codes', '关联券码')
            ->onlyOnDetail()
            ->setTemplatePath('admin/channel/codes.html.twig')
            ->setHelp('通过此渠道发放的券码列表');

        yield AssociationField::new('coupons', '关联优惠券')
            ->onlyOnDetail()
            ->setTemplatePath('admin/channel/coupons.html.twig')
            ->setHelp('可在此渠道投放的优惠券列表');

        // 统计字段
        yield TextField::new('codesCount', '券码数量')
            ->onlyOnIndex()
            ->formatValue(function ($value, $entity) {
                return number_format($entity->getCodes()->count());
            })
            ->setHelp('通过此渠道发放的券码总数');

        yield TextField::new('couponsCount', '优惠券数量')
            ->onlyOnIndex()
            ->formatValue(function ($value, $entity) {
                return number_format($entity->getCoupons()->count());
            })
            ->setHelp('可在此渠道投放的优惠券总数');

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

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('title', '渠道标题'))
            ->add(TextFilter::new('code', '渠道编码'))
            ->add(TextFilter::new('remark', '渠道描述'))
            ->add(TextFilter::new('appId', '小程序AppID'))
            ->add(BooleanFilter::new('valid', '有效状态'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('updateTime', '更新时间'));
    }

    /**
     * 优化列表查询，预加载关联数据用于统计
     */
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->leftJoin('entity.codes', 'codes')
            ->leftJoin('entity.coupons', 'coupons')
            ->groupBy('entity.id')
            ->orderBy('entity.id', 'DESC');
    }
} 