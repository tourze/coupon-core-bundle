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
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Enum\CodeStatus;

class CodeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Code::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('券码')
            ->setEntityLabelInPlural('券码管理')
            ->setPageTitle('index', '券码列表')
            ->setPageTitle('new', '新建券码')
            ->setPageTitle('edit', fn (Code $code) => sprintf('编辑券码 #%d', $code->getId()))
            ->setPageTitle('detail', fn (Code $code) => sprintf('券码详情 #%d', $code->getId()))
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'sn', 'remark'])
            ->setHelp('index', '这里展示了所有的券码信息，包括未使用、已使用和已过期的券码。');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('id', 'ID'))
            ->add(TextFilter::new('sn', '券码'))
            ->add(EntityFilter::new('coupon', '优惠券'))
            ->add(EntityFilter::new('channel', '投放渠道'))
            ->add(EntityFilter::new('gatherChannel', '领取渠道'))
            ->add(EntityFilter::new('useChannel', '使用渠道'))
            ->add(ChoiceFilter::new('status', '状态')->setChoices(array_combine(
                array_map(fn(CodeStatus $status) => $status->getLabel(), CodeStatus::cases()),
                array_map(fn(CodeStatus $status) => $status->value, CodeStatus::cases())
            )))
            ->add(DateTimeFilter::new('gatherTime', '领取时间'))
            ->add(DateTimeFilter::new('expireTime', '过期时间'))
            ->add(DateTimeFilter::new('useTime', '使用时间'))
            ->add(BooleanFilter::new('valid', '有效'))
            ->add(BooleanFilter::new('locked', '锁定'))
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        // 基本信息
        yield FormField::addTab('基本信息')->setIcon('fa fa-info-circle');
        yield IdField::new('id')->setMaxLength(9999);
        yield TextField::new('sn', '券码');
        yield AssociationField::new('coupon', '优惠券');
        yield AssociationField::new('owner', '拥有用户');
        yield ChoiceField::new('status', '状态')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions([
                'class' => CodeStatus::class,
                'choice_label' => fn(CodeStatus $status) => $status->getLabel(),
            ])
            ->formatValue(fn ($value) => $value instanceof CodeStatus ? $value->getLabel() : '')
            ->hideOnForm();
        yield IntegerField::new('consumeCount', '核销次数')
            ->setRequired(false);
        yield TextField::new('remark', '备注')
            ->setRequired(false)
            ->hideOnIndex();

        // 渠道信息
        yield FormField::addTab('渠道信息')->setIcon('fa fa-share-alt');
        yield AssociationField::new('channel', '投放渠道')
            ->setFormTypeOptions([
                'choice_label' => 'title',
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('c')
                        ->where('c.valid = :valid')
                        ->setParameter('valid', true)
                        ->orderBy('c.title', 'ASC');
                },
            ]);
        yield AssociationField::new('gatherChannel', '领取渠道')
            ->setFormTypeOptions([
                'choice_label' => 'title',
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('c')
                        ->where('c.valid = :valid')
                        ->setParameter('valid', true)
                        ->orderBy('c.title', 'ASC');
                },
            ]);
        yield AssociationField::new('useChannel', '使用渠道')
            ->setFormTypeOptions([
                'choice_label' => 'title',
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('c')
                        ->where('c.valid = :valid')
                        ->setParameter('valid', true)
                        ->orderBy('c.title', 'ASC');
                },
            ]);

        // 时间信息
        yield FormField::addTab('时间信息')->setIcon('fa fa-clock');
        yield DateTimeField::new('gatherTime', '领取时间');
        yield DateTimeField::new('expireTime', '过期时间');
        yield DateTimeField::new('useTime', '使用时间');
        yield DateTimeField::new('activeTime', '激活时间');

        // 状态信息
        yield FormField::addTab('状态信息')->setIcon('fa fa-toggle-on');
        yield BooleanField::new('needActive', '需要激活');
        yield BooleanField::new('active', '已激活');
        yield BooleanField::new('valid', '有效');
        yield BooleanField::new('locked', '锁定');

        // 系统信息
        yield FormField::addTab('系统信息')->setIcon('fa fa-cog');
        yield DateTimeField::new('createTime', '创建时间')->hideOnForm();
        yield DateTimeField::new('updateTime', '更新时间')->hideOnForm();
        yield TextField::new('createdBy', '创建人')->hideOnForm();
        yield TextField::new('updatedBy', '更新人')->hideOnForm();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, Action::EDIT, Action::DELETE])
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->displayIf(static function (Code $code) {
                    return $code->getStatus() === CodeStatus::UNUSED;
                });
            });
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        return $qb
            ->leftJoin('entity.coupon', 'coupon')
            ->leftJoin('entity.channel', 'channel')
            ->leftJoin('entity.gatherChannel', 'gatherChannel')
            ->leftJoin('entity.useChannel', 'useChannel')
            ->leftJoin('entity.owner', 'owner')
            ;
    }
}
