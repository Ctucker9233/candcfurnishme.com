<?php

namespace App\Field;

use App\Form\SubtotalType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;

class SubtotalField implements FieldInterface
{
    use FieldTrait;
    public static function new(string $propertyName, ?string $label = "Subtotal")
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(SubtotalType::class)
            ->setDefaultColumns('col-md-4 col-xxl-3');
    }
}  
