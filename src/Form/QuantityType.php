<?php

namespace App\Form;

use App\Entity\Sale;
use App\Entity\SaleLineItems;
use App\Entity\Inventory;
use App\Repository\InventoryRepository;
use App\Form\SaleItemType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
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

class QuantityType extends AbstractType
{

    public function __construct(private EntityManagerInterface $entityManager, private InventoryRepository $inventoryRepository)
    {

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
        $builder->add('quantity', NumberType::class)
                ->add('item', EntityType::class, [
                    'class' => Inventory::class,
                    'attr' => ['data-ea-widget' => 'ea-autocomplete'],
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('i')
                            ->where('i.discontinued = :discontinued')
                            ->setParameter('discontinued', false);    
                    },
                ]);
                //->add('price', NumberType::class);

                $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($user): void {
                    if (null !== $event->getData()) {
                        // we don't need to add the friend field because
                        // the message will be addressed to a fixed friend
                        return;
                    }
                    $sale = $event->getEntityInstance();
                    dd($sale);
                    $form = $event->getForm();
                    $item = $event->getData()->getItem();
                    dd($item);
                    $formOptions = [
                        'class' => Inventory::class,
                        'choice_label' => 'price',
                        'query_builder' => function (InventoryRepository $inventoryRepository) use ($user): void {
                            // call a method on your repository that returns the query builder
                            // return $userRepository->createFriendsQueryBuilder($user);
                        },
                    ];
        
                    // create the field, this is similar the $builder->add()
                    // field name, field type, field options
                    $form->add('price', EntityType::class, $formOptions);
                });
                
        
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event): void {
                    
                $form = $event->getForm();
                // this would be your entity, i.e. SportMeetup 
                $data = $form->getData();
                var_dump($data);
                die("end");
                 
                $formModifier($event->getForm(), $data->getItem());

                if($data !== null){
                    $price = $data->getItem()->getPrice();
                    //dd($name);
                    $form->add('price', EntityType::class, [
                        'class' => Inventory::class,
                        'choices' => $price,
                    ]); 
                }
                 
            });
        
        //         $builder->get('price')->addEventListener(
        //             FormEvents::POST_SUBMIT,
        //             function (FormEvent $event) use ($formModifier) {
        //                 // It's important here to fetch $event->getForm()->getData(), as
        //                 // $event->getData() will get you the client data (that is, the ID)
        //                 $price = $event->getForm()->getData();
        
        //                 // since we've added the listener to the child, we'll have to pass on
        //                 // the parent to the callback functions!
        //                 $formModifier($event->getForm()->getParent(), $saleLineItems);  // Here line 58 and error show this line
        //             }
        //         );
        
             $builder->setAction($options['action']);
    }

    

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SaleLineItems::class,
            'compound' => true
            
        ]);
    }
}