<?php

namespace App\Controller\Admin;

use App\Entity\Sale;
use App\Entity\User;
use App\Entity\Customer;
use App\Entity\SaleItems;
use App\Entity\Inventory;
use App\Entity\Packages;
use App\Form\SaleItemType;
use App\Form\ItemFormType;
use App\Form\QuantityType;
use App\Form\SubtotalType;
use App\Field\SaleItemField;
use App\Field\SubtotalField;
use Doctrine\ORM\QueryBuilder;
use App\Repository\InventoryRepository;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SaleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Sale::class;
    }
    
    public function __construct(private InventoryRepository $inventoryRepository){

    }

    public function configureFields(string $pageName): iterable
    {
            yield IdField::new('id')->onlyOnIndex();
            yield IdField::new('externalSalesOrderNumber', 'Sale Number')
                ->setColumns(6);
            yield IdField::new('BcOrderNumber')
                ->setFormTypeOption(
                    'disabled',
                    true
                )
                ->setColumns(6); 
            yield TextField::new('BcStatus')->setColumns(6)
                    ->setFormTypeOption(
                        'disabled',
                        true
                    );
            $PsStatus = [ 'Pending', 'Ready', 'Complete' ];
            yield ChoiceField::new( 'PsStatus' )
                ->setChoices( array_combine( $PsStatus, $PsStatus ) )
                ->renderAsBadges()
                ->setColumns(6);
            $order = [ 'REGULAR SALE', 'QUOTE', 'SERVICE' ];
            yield ChoiceField::new( 'OrderType' )
                    ->setChoices( array_combine( $order, $order ) )
                    ->renderAsBadges()
                    ->setColumns(6);
            yield FormField::addPanel('Sale Information')->collapsible();
            yield AssociationField::new('salesperson')
                ->setCrudController(UserCrudController::class)
                ->autocomplete()
                ->setColumns(6);
            yield AssociationField::new('customer')
                ->setCrudController(CustomerCrudController::class)
                ->autocomplete()
                ->formatValue(static function ($value, Sale $sale): ?string {
                    if (!$customer = $sale->getCustomer()) {
                        return null;
                    }
                    return sprintf('%s&nbsp;(%s)', $customer->getName(), $customer->getSalesOrders()->count());
                })
                ->setFormTypeOption(
                    'disabled',
                    $pageName !== Crud::PAGE_NEW
                )
                ->setColumns(6);
            yield DateField::new('deliveryDate')->setColumns(6);
            $delivery = [ 'OD', 'CPU' ];
            yield ChoiceField::new('shipViaCode')->setColumns(6)
                ->setChoices( array_combine( $delivery, $delivery ) )
                ->renderAsBadges();
            $payment = [ 'Cash', 'Check', 'Visa/MC', 'Other' ];
            yield ChoiceField::new('paymentMethod')
                ->setChoices( array_combine( $payment, $payment ) )
                ->renderAsBadges();
            
            yield NumberField::new('saleAmount')
                ->setFormTypeOption(
                    'disabled',
                    true
                )
                ->setFormTypeOptions([
                    'row_attr' => [
                        'data-controller' => 'subtotal_controller',
                    ],
                    'attr' => [
                        'data-action' => 'subtotal#render',
                    ],
                ]);
            yield NumberField::new('DeliveryAmount')->hideOnIndex();
            yield TextField::new('taxCode');
            $tax = [7.75, 8.25, 8.5, 8.75];
            yield ChoiceField::new('TaxPercentage', 'Tax Percentage')
                ->hideOnIndex()
                ->setChoices( array_combine( $tax, $tax ) );
            yield NumberField::new('taxAmount')
                ->setFormTypeOption(
                    'disabled',
                    true
                );
            
            yield NumberField::new('totalAmount')->hideOnIndex();
            
            yield FormField::addPanel('Items')->collapsible();
            yield CollectionField::new('SaleLineItems')
                ->setEntryType(QuantityType::class)
                ->setFormTypeOptions([
                    'by_reference' => false,
                    'attr' => [
                        'data-subtotal-target' => 'subtotal',
                    ],
                ])
                ->setColumns(12);
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

    // public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    // {
    //     $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);

    //     return $this->addTotalsEventListener($formBuilder);
    // }

    // private function addTotalsEventListener(FormBuilderInterface $formBuilder): FormBuilderInterface
    // {
    //     return $formBuilder->addEventListener(FormEvents::PRE_SUBMIT, $this->setTotals());
    // }

    // private function setTotals() {
    //     return function($event) {
    //         $form = $event->getForm();

    //         $id = $form->get('id');
    //         $form->get('externalSalesOrderNumber')->setExternalSalesOrderNumber($id + 100000);
    //         $items = $form->get('SaleLineItems')->getData();
    //         if ($items !== null) {
                
            
    //         $prices = array();

    //         foreach($data as $item){
    //             //dump($item);
    //             $itm = $this->inventoryRepository->findOneBy(['id' => $item->getItem()]);
    //             dump($itm);
    //             $price = $itm->getPrice() /100;
    //             array_push($prices, $price);
    //         }
    //         $subtotal = array_sum($prices);
    //         //dump($subtotal);
    //         $ship = $form->getData()->getShipViaCode();
    //         if($ship === 'OD'){
    //             $form->getData()->setDeliveryAmount(95);
    //         }
    //         else{
    //             $form->getData()->setDeliveryAmount(0);
    //         }
    //         $delivery = $form->getData()->getDeliveryAmount();
    //         //dump($delivery);

    //         $salestax = $form->getData()->getTaxPercentage();
    //         $tax = ($subtotal + $delivery) * $salestax;
    //         $total = $subtotal + $delivery + $tax;
    //         $form->getData()->setSaleAmount($subtotal);
    //         $form->getData()->setTaxAmount($tax);
    //         $form->getData()->setTotalAmount($total);
        
            
    //         };
    //         $form->getData()->setSaleAmount(0.0);
    //         $form->getData()->setDeliveryAmount(0.0);
    //         $form->getData()->setTotalAmount(0.0);
    //     };
    // }  
                
    
}
