<?php

namespace App\Controller\Admin;

use App\Entity\Packages;
use App\Entity\Inventory;
use App\Entity\Sale;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;

class PackagesCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Packages::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            yield IdField::new('id')->onlyOnIndex(),
            yield ImageField::new('PackPicture', 'Picture')->onlyOnIndex(),
            yield TextField::new('PackPicture')->hideOnIndex(),
            yield TextField::new('packageId')->setColumns(6),
            yield IdField::new('BcPackId')->setColumns(6),
            yield TextField::new('description')->setColumns(6),
            yield TextField::new('BcPackDescription')->setColumns(6)->hideOnIndex(),
            yield ArrayField::new('componentIds'),
            yield NumberField::new('pkgQuantity'),
            yield MoneyField::new('price')
                ->setCurrency('USD')
                ->setStoredAsCents(),
            yield BooleanField::new('active'),
            yield ArrayField::new('componentQuantity')
                ->hideOnIndex()
                ->setColumns(6),
            yield AssociationField::new('itemIds')
                ->setCrudController(InventoryCrudController::class)
                ->autocomplete()
                ->setFormTypeOption('by_reference', false)
                ->setColumns(6),
            yield AssociationField::new('PackageSales')
                ->setCrudController(SaleItemsCrudController::class)
                ->autocomplete()
                ->setFormTypeOption('by_reference', false),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add('active');
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
}
