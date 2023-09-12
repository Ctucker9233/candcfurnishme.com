<?php

namespace App\Form;

use App\Entity\Sale;
use App\Entity\SaleLineItems;
use App\Entity\Inventory;
use App\Form\SaleItemType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\AssociationType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ArrayType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\CallbackTransformer;
use Doctrine\ORM\EntityManagerInterface;

class SubtotalType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('saleAmount');

        $formModifier = function (FormInterface $form, $data) {
            $prices = array();
            if($data !== null){
                foreach($data as $item){
                    array_push($prices, $item->getItem()->getPrice());
                }
                $subtotal = array_sum($prices);
                $form->get('saleAmount')->setData($subtotal);
            }
            else{
                $subtotal = 0;
                $form->get('saleAmount')->setData($subtotal);
            }
        };

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function(FormEvent $event) {
                $form = $event->getForm();
                $sale = $event->getEntityInstance();
        //dump($sale);
                $id = $sale['id'];
                $sale['ExternalSalesOrderNumber'] = $id + 100000;
                $data = $sale['SaleLineItems'];
        
                if($data !== null){
                    $prices = array();
                    foreach($data as $item){
                        //dump($item);
                        $repository = $this->entityManager->getRepository(Inventory::class);
                        $itm = $repository->findOneBy(['id' => $item['item']]);
                        dump($itm);
                        $price = $itm->getPrice();
                        array_push($prices, $price);
                    }
                    $subtotal = array_sum($prices);
                    if($sale['ShipViaCode'] === 'OD'){
                        $sale['DeliveryAmount'] = 95;
                    }
                    else{
                        $sale['DeliveryAmount'] = 0;
                    }
                    $delivery = $sale['DeliveryAmount'];
                    $tax = ($subtotal + $delivery) * ($sale['TaxPercentage']/100);
                    $total = $subtotal + $delivery + $tax;
                    $sale['SaleAmount'] = $subtotal;
                    $sale['TaxAmount'] = $tax;
                    $sale['TotalAmount'] = $total;
                }
                else{
                    $subtotal = 0;
                    $sale['DeliveryAmount'] = 0;
                    $sale['SaleAmount'] = $subtotal;
                    $sale['TaxAmount'] = $subtotal;
                    $sale['TotalAmount'] = $subtotal;
                };
            }
        );

        $builder->get('saleAmount')->addEventListener(
            FormEvents::POST_SUBMIT,
            function(FormEvent $event) {
                $form = $event->getForm();
                $subtotal = $event->getForm()->getData();
                $form->getParent()->get('saleAmount')->setData($subtotal);
            }
        );

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sale::class
        ]);
    }

    public function getSpecificLocationSelect(Request $request)
    {
        $sale = new Sale();
        $sale->setSaleAmount($request->query->get('SaleAmount'));


        #dd($article->getLocation());

        if($article->getLocation() === ''){
            return new Response(null, 204);
        }
        
        $form = $this->createForm(ArticleFormType::class, $article);


        // specificLocationName no field? Return an empty response
        if (!$form->has('specificLocationName')) {
            return new Response(null, 204);
        }


        return $this->render('article_admin/_specific_location_name.html.twig', [
            'articleForm' => $form->createView(),
        ]);
    }
}