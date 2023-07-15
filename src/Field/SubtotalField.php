<?php

namespace App\Field;

use App\Form\SubtotalType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;

final class SubtotalField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, ?string $label = 'Subtotal'): self
    {
        return (new self())
            ->setProperty($propertyName)
            // this template is used in 'index' and 'detail' pages
            // this is used in 'edit' and 'new' pages to edit the field contents
            // you can use your own form types too
            ->setFormType(SubtotalType::class)
            ->setcolumns(3)
        ;
    }   
}