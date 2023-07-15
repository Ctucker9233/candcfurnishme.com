<?php

namespace App\Field;

use App\Form\ItemType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;

final class SaleItemField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, ?string $label = 'Item'): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            // this template is used in 'index' and 'detail' pages
            // this is used in 'edit' and 'new' pages to edit the field contents
            // you can use your own form types too
            ->setFormType(ItemType::class)
            ->setcolumns(9)
            ->allowAdd()
            ->allowDelete()
            ->autocomplete()
        ;
    }   
}