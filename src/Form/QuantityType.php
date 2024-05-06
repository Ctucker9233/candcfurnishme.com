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
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;
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
        $builder =  new DynamicFormBuilder($builder);

        $builder->add('quantity', NumberType::class, ['label' => 'Quantity'])
                ->add('item', EntityType::class, [
                    'class' => Inventory::class,
                    'label' => 'Item',
                    'attr' => ['data-ea-widget' => 'ea-autocomplete'],
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('i')
                            ->where('i.discontinued = :discontinued')
                            ->setParameter('discontinued', false);    
                    },
                ]);
        // $builder->addDependent('quantity', 'item', function(DependentField $field, ?int $item) {
        //             if (null === $item) {
        //                 return; // field not needed
        //             }
        //             dd($item);
        //             $field->add(ChoiceType::class, [
        //                 'label' => 'What went wrong?',
        //                 'attr' => ['rows' => 3],
        //                 'help' => sprintf('Because you gave a %d rating, we\'d love to know what went wrong.', $rating),
        //             ]);
        //         });
                

                // $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($user): void {
                //     if (null !== $event->getData()) {
                //         // we don't need to add the friend field because
                //         // the message will be addressed to a fixed friend
                //         $price = 0;
                //         if(null !==$event->getData()->getItem()){
                //             $rawPrice = $event->getData()->getItem()->getPrice();
                //             $price = $rawPrice/100; 
                            
                //             $form = $event->getForm();
                //             $item = $event->getData()->getItem();
                //             $formOptions = [
                //                 'label' => 'Price',
                //                 'empty_data' => $price,
                //             ];
        
                //         // create the field, this is similar the $builder->add()
                //         // field name, field type, field options
                //             $form->remove('price');
                //             $form->add('price', NumberType::class, $formOptions);
                //             return;
                //         }
                //     }
                // });
                
                // $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
                //     $item = $event->getData()->getItem();
                //     $form = $event->getForm();
                    
            
                //     // checks whether the user has chosen to display their email or not.
                //     // If the data was submitted previously, the additional value that is
                //     // included in the request variables needs to be removed.
                //     if ($item !== null) {
                //         $rawPrice = $event->getData()->getItem()->getPrice();
                //         $price = $rawPrice/100;

                //         $form->remove('price');
                //         $form->add('price', NumberType::class, $formOptions);
                //     } else {
                //         $price = 0;
                //         $form->get('price')->setData($price);
                //     }
                // });

    //             $builder->get('price')->addEventListener(
    //                 FormEvents::POST_SUBMIT,
    //                 function (FormEvent $event) use ($formModifier) {
    //                      // It's important here to fetch $event->getForm()->getData(), as
    //                      // $event->getData() will get you the client data (that is, the ID)
    //                     $price = $event->getForm()->getData();
        
    //                     // since we've added the listener to the child, we'll have to pass on
    //                     // the parent to the callback functions!
    //                     $formModifier($event->getForm()->getParent(), $saleLineItems);
    //                 }
    //             );
        
    //          $builder->setAction($options['action']);
    }

    

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SaleLineItems::class,
            'compound' => true

        ]);
    }
}