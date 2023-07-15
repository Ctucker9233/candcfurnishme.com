<?php

namespace App\Controller\Admin;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;


class CustomerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Customer::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Customer')
            ->setEntityLabelInPlural('Customer List')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            yield IdField::new('id')->onlyOnIndex(),
            yield TextField::new('name')->onlyOnIndex(),
            yield TextField::new('firstname')->hideOnIndex(),
            yield TextField::new('lastname')->hideOnIndex(),
            yield FormField::addPanel('Billing Address')->collapsible()->addCssClass('billing-address'),
            yield TextField::new('address')->onlyOnIndex()->setColumns(6),
            yield TextField::new('address1')->hideOnIndex()->setColumns(6),
            yield TextField::new('address2')->hideOnIndex()->setColumns(6),
            yield TextField::new('city')->setColumns(4),
            yield TextField::new('state')->setColumns(4),
            yield TextField::new('postalcode')->setColumns(4),
            yield FormField::addPanel('Shipping Address')->collapsible()->setColumns(6)->addCssClass('shipping-address'),
            yield TextField::new('shippingAddress1')->hideOnIndex()->setColumns(6),
            yield TextField::new('shippingAddress2')->hideOnIndex()->setColumns(6),
            yield TextField::new('shippingCity')->hideOnIndex()->setColumns(4),
            yield TextField::new('shippingState')->hideOnIndex()->setColumns(4),
            yield TextField::new('shippingPostalcode')->hideOnIndex()->setColumns(4),
            yield FormField::addPanel('Contact')->collapsible()->addCssClass('contact'),
            yield TextField::new('phone1')->setColumns(4),
            yield TextField::new('phone2')->hideOnIndex()->setColumns(4),
            yield TextField::new('phone3')->hideOnIndex()->setColumns(4),
            yield EmailField::new('email'),
            yield BooleanField::new( 'is_deleted' )->renderAsSwitch(false),
            yield CollectionField::new('salesOrders')
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add('isDeleted')
            ->add('name');
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->setPermission(Action::INDEX, 'ROLE_SALES')
            ->setPermission(Action::NEW, 'ROLE_SALES')
            ->setPermission(Action::DETAIL, 'ROLE_SALES')
            ->setPermission(Action::EDIT, 'ROLE_SALES')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
            ->setPermission(Action::BATCH_DELETE, 'ROLE_ADMIN');
    }
    
}
