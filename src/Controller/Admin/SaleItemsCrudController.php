<?php

namespace App\Controller\Admin;

use App\Entity\SaleItems;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class SaleItemsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SaleItems::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
