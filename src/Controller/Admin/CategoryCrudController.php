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
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\CouponCoreBundle\Entity\Category;

/**
 * 优惠券分类管理控制器
 */
class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('优惠券分类')
            ->setEntityLabelInPlural('优惠券分类管理')
            ->setPageTitle('index', '分类列表')
            ->setPageTitle('new', '新建分类')
            ->setPageTitle('edit', '编辑分类')
            ->setPageTitle('detail', '分类详情')
            ->setHelp('index', '管理优惠券分类，支持树形结构和层级管理')
            ->setDefaultSort(['sortNumber' => 'DESC', 'id' => 'DESC'])
            ->setSearchFields(['id', 'title', 'description', 'remark']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, Action::EDIT, Action::DELETE])
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('新建分类');
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
            ->setMaxLength(9999);

        yield TextField::new('title', '分类名称')
            ->setRequired(true)
            ->setMaxLength(60)
            ->setColumns(12)
            ->setHelp('分类的显示名称');

        // 树形结构字段
        yield AssociationField::new('parent', '上级分类')
            ->setRequired(false)
            ->autocomplete()
            ->setColumns(12)
            ->setHelp('选择上级分类，留空表示顶级分类');

        // 图片字段
        yield ImageField::new('logoUrl', 'LOGO地址')
            ->setBasePath('/uploads')
            ->setUploadDir('public/uploads')
            ->setUploadedFileNamePattern('[year]/[month]/[day]/[contenthash].[extension]')
            ->setRequired(false)
            ->setColumns(12)
            ->setHelp('分类的LOGO图片');

        // 富文本描述
        yield TextEditorField::new('description', '简介')
            ->setRequired(false)
            ->hideOnIndex()
            ->setColumns(12)
            ->setHelp('分类的详细描述，支持富文本编辑');

        // 备注字段
        yield TextareaField::new('remark', '备注')
            ->setRequired(false)
            ->setMaxLength(100)
            ->hideOnIndex()
            ->setColumns(12);

        // 显示标签字段（JSON数组）
        yield ChoiceField::new('showTags', '显示标签')
            ->setRequired(false)
            ->allowMultipleChoices()
            ->autocomplete()
            ->hideOnIndex()
            ->setColumns(12)
            ->setHelp('选择要显示的标签，支持多选')
            ->setChoices($this->getShowTagsChoices());

        // 时间字段
        yield DateTimeField::new('startTime', '开始有效时间')
            ->setRequired(false)
            ->setColumns(6)
            ->hideOnIndex();

        yield DateTimeField::new('endTime', '截止有效时间')
            ->setRequired(false)
            ->setColumns(6)
            ->hideOnIndex();

        // 排序字段
        yield IntegerField::new('sortNumber', '排序值')
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('数值越大排序越靠前，默认为0');

        // 状态字段
        yield BooleanField::new('valid', '有效状态')
            ->setRequired(false)
            ->setColumns(6);

        // 关联信息（仅详情页显示）
        yield AssociationField::new('children', '下级分类')
            ->onlyOnDetail()
            ->setTemplatePath('admin/category/children.html.twig')
            ->setHelp('当前分类的所有下级分类');

        yield AssociationField::new('coupons', '关联优惠券')
            ->onlyOnDetail()
            ->setTemplatePath('admin/category/coupons.html.twig')
            ->setHelp('使用此分类的优惠券列表');

        // 统计字段
        yield TextField::new('nestTitle', '层级标题')
            ->onlyOnIndex()
            ->setHelp('包含上级分类的完整路径');

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
            ->add(TextFilter::new('title', '分类名称'))
            ->add(EntityFilter::new('parent', '上级分类'))
            ->add(BooleanFilter::new('valid', '有效状态'))
            ->add(NumericFilter::new('sortNumber', '排序值'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('updateTime', '更新时间'))
            ->add(DateTimeFilter::new('startTime', '开始有效时间'))
            ->add(DateTimeFilter::new('endTime', '截止有效时间'));
    }

    /**
     * 优化列表查询，预加载关联数据
     */
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->leftJoin('entity.parent', 'parent')
            ->addSelect('parent')
            ->leftJoin('entity.children', 'children')
            ->addSelect('children')
            ->orderBy('entity.sortNumber', 'DESC')
            ->addOrderBy('entity.id', 'DESC');
    }

    /**
     * 获取显示标签的选择项
     * 注意：这里需要根据实际的标签获取服务来实现
     */
    private function getShowTagsChoices(): array
    {
        // TODO: 这里应该从 'product.tag.fetcher' 服务获取真实的标签选项
        // 现在返回一些示例选项
        return [
            '热门' => 'hot',
            '新品' => 'new',
            '限时' => 'limited',
            '推荐' => 'recommend',
            '特价' => 'special',
        ];
    }
} 