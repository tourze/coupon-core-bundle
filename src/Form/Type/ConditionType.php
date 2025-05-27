<?php

namespace Tourze\CouponCoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tourze\ConditionSystemBundle\Entity\BaseCondition;

/**
 * 条件表单类型
 */
class ConditionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', HiddenType::class)
            ->add('label', TextType::class, [
                'label' => '条件名称',
                'required' => true,
                'attr' => [
                    'placeholder' => '请输入条件名称',
                    'class' => 'form-control'
                ]
            ])
            ->add('remark', TextareaType::class, [
                'label' => '备注',
                'required' => false,
                'attr' => [
                    'placeholder' => '可选的备注信息',
                    'rows' => 2,
                    'class' => 'form-control'
                ]
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => '启用状态',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BaseCondition::class,
        ]);
    }
} 