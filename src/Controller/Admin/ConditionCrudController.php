<?php

namespace Tourze\CouponCoreBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Tourze\CouponCoreBundle\Entity\BaseCondition;
use Tourze\CouponCoreBundle\Service\ConditionHandlerFactory;

/**
 * 条件管理控制器
 */
class ConditionCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly ConditionHandlerFactory $handlerFactory
    ) {}

    public static function getEntityFqcn(): string
    {
        return BaseCondition::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('条件')
            ->setEntityLabelInPlural('条件管理')
            ->setPageTitle('index', '条件列表')
            ->setPageTitle('new', '新建条件')
            ->setPageTitle('edit', '编辑条件')
            ->setPageTitle('detail', '条件详情')
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('新建条件');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setLabel('编辑');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setLabel('删除');
            })
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setLabel('查看');
            });
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->onlyOnIndex();

        yield AssociationField::new('coupon', '优惠券')
            ->setRequired(true)
            ->autocomplete();

        yield ChoiceField::new('type', '条件类型')
            ->setChoices($this->getConditionTypeChoices())
            ->setRequired(true)
            ->renderExpanded(false);

        yield TextField::new('label', '条件名称')
            ->setRequired(true)
            ->setMaxLength(100);

        yield BooleanField::new('enabled', '启用状态')
            ->setRequired(false);

        yield TextareaField::new('remark', '备注')
            ->setRequired(false)
            ->hideOnIndex();

        yield DateTimeField::new('createTime', '创建时间')
            ->onlyOnDetail();

        yield DateTimeField::new('updateTime', '更新时间')
            ->onlyOnDetail();
    }

    /**
     * 获取条件类型选择项
     */
    private function getConditionTypeChoices(): array
    {
        $choices = [];
        
        foreach ($this->handlerFactory->getAllHandlers() as $handler) {
            $choices[$handler->getLabel()] = $handler->getType();
        }

        return $choices;
    }
} 