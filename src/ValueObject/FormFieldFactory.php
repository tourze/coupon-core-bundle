<?php

namespace Tourze\CouponCoreBundle\ValueObject;

/**
 * 表单字段工厂
 */
class FormFieldFactory
{
    public static function text(string $name, string $label): FormField
    {
        return FormField::create($name, 'text', $label);
    }

    public static function integer(string $name, string $label): FormField
    {
        return FormField::create($name, 'integer', $label);
    }

    public static function decimal(string $name, string $label, int $scale = 2): FormField
    {
        return FormField::create($name, 'decimal', $label)
            ->options(['scale' => $scale]);
    }

    public static function boolean(string $name, string $label): FormField
    {
        return FormField::create($name, 'boolean', $label);
    }

    public static function choice(string $name, string $label, array $choices = []): FormField
    {
        return FormField::create($name, 'choice', $label)
            ->choices($choices);
    }

    public static function array(string $name, string $label): FormField
    {
        return FormField::create($name, 'array', $label);
    }

    public static function textarea(string $name, string $label): FormField
    {
        return FormField::create($name, 'textarea', $label);
    }

    public static function date(string $name, string $label): FormField
    {
        return FormField::create($name, 'date', $label);
    }

    public static function datetime(string $name, string $label): FormField
    {
        return FormField::create($name, 'datetime', $label);
    }

    public static function email(string $name, string $label): FormField
    {
        return FormField::create($name, 'email', $label);
    }

    public static function url(string $name, string $label): FormField
    {
        return FormField::create($name, 'url', $label);
    }
}
