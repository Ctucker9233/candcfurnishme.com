<?php

namespace App\Controller\Admin;

use App\Entity\Vendors;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

class VendorsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Vendors::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            yield IdField::new('id')->onlyOnIndex(),
            yield IdField::new('BCId'),
            yield ImageField::new('brandImage')->onlyOnIndex(),
            yield TextEditorField::new('brandImage')->hideOnIndex(),
            yield TextField::new('vendorId')
                ->setFormTypeOption(
                    'disabled',
                    $pageName !== Crud::PAGE_NEW
                ),
            yield TextField::new('vendorName'),
            yield TextField::new('rep'),
            yield EmailField::new('email'),
            yield TextField::new('pageTitle'),
            yield TextField::new('brandUrl'),
            yield BooleanField::new('active'),
            yield TextField::new('command'),
        ];
    }
    
    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->setPermission(Action::INDEX, 'ROLE_SALES')
            ->setPermission(Action::NEW, 'ROLE_ADMIN')
            ->setPermission(Action::DETAIL, 'ROLE_SALES')
            ->setPermission(Action::EDIT, 'ROLE_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
            ->setPermission(Action::BATCH_DELETE, 'ROLE_ADMIN');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add('active');
    }

}
