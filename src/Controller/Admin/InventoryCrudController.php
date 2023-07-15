<?php

namespace App\Controller\Admin;

use App\Entity\Inventory;
use App\Entity\Sale;
use App\Entity\SaleLineItems;
use App\Entity\ItemLocation;
use App\Entity\Packages;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class InventoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Inventory::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            yield FormField::addTab('Item Information'),
            yield IdField::new('id')->onlyOnIndex(),
            yield ImageField::new('pictureLink', 'Image')->onlyOnIndex(),
            yield TextField::new('pictureLink')->hideOnIndex(),
            yield FormField::addPanel('Item Information')->collapsible(),
            yield IdField::new('BCItemId', "BC ID")
                ->setColumns(6),
            yield TextField::new('itemID', 'Item ID')
                ->setFormTypeOption(
                    'disabled',
                    $pageName !== Crud::PAGE_NEW
                )
                ->setColumns(6),
            yield TextField::new('itemDescription', 'Description')->setColumns(6),
            yield TextField::new('BcItemDescription')->setColumns(6)->hideOnIndex(),
            yield TextField::new('mfcsku', 'Manufacturor SKU')->setColumns(6),
            yield TextField::new('upcCode', 'UPC Code')->setColumns(6)->hideOnIndex(),
            yield NumberField::new('quantity', 'Quantity Available')->setColumns(3),
            yield TextField::new('vendorName', 'Vendor')->setColumns(3),
            yield MoneyField::new('price')
                ->setCurrency('USD')
                ->setStoredAsCents()
                ->setColumns(3),
            yield FormField::addPanel('Settings')->collapsible(),
            yield BooleanField::new('isDeleted', 'Is Deleted'),
            yield BooleanField::new('webHide', 'Hide on Web'),
            yield BooleanField::new('discontinued'),
            yield FormField::addTab('Sale Information'),
            yield AssociationField::new('SaleLineItems', 'Sales')->hideOnIndex()
                ->setFormTypeOption('by_reference', false),
            yield FormField::addTab('Stock'),
            yield AssociationField::new('stock')
                ->setCrudController(ItemLocationCrudController::class)
                ->autocomplete()
                ->setFormTypeOption('by_reference', false)
                ->hideOnIndex(),
            yield FormField::addTab('Packages'),
            yield AssociationField::new('Package')
                ->setCrudController(PackagesCrudController::class)
                ->autocomplete()
                ->setFormTypeOption('by_reference', false)
                ->hideOnIndex(),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add('isDeleted')
            ->add('discontinued')
            ->add('webHide');
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
