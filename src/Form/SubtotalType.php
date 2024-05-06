<?php

namespace App\Form;

use App\Entity\Sale;
use App\Entity\SaleLineItems;
use App\Entity\Inventory;
use App\Form\SaleItemType;
use App\Form\DataTransformer\SaleToStringTransformer;
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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\CallbackTransformer;
use Doctrine\ORM\EntityManagerInterface;

class SubtotalType extends AbstractType
{

    public function __construct(private EntityManagerInterface $entityManager, private SaleToStringTransformer $transformer,)
    {
        $this->entityManager = $entityManager;
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('saleAmount', NumberType::class)
                ->add('save', SubmitType::class, ['label' => 'Update Subtotal']);
        
        $builder->get('saleAmount')->addModelTransformer($this->transformer);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($user): void {
            if ($event->getForm()->getParent()->getData()->getSaleLineItems()['elements'] !== null) {
                // we don't need to add the friend field because
                // the message will be addressed to a fixed friend
                $subtotal = 0;
                //dd($event->getForm()->getParent()->getData()->getSaleLineItems());
                $items = $event->getForm()->getParent()->getData()->getSaleLineItems();
                dd($items['elements']);
                $price = $rawPrice/100; 
                    
                $form = $event->getForm();
                $item = $event->getData()->getItem();
                $formOptions = [
                    'empty_data' => $subtotal,
                ];

                // create the field, this is similar the $builder->add()
                // field name, field type, field options
                    $form->remove('saleAmount');
                    $form->add('saleAmount', NumberType::class, $formOptions);
                
            }
            else{
                $subtotal = 0;
                $form = $event->getForm();
                $formOptions = [
                    'empty_data' => $subtotal,
                ];
                
                $form->add('saleAmount', NumberType::class, $formOptions);
            }
        });
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
            $sale = $event->getForm()->getParent()->getData();
            $form = $event->getForm();
    
            // checks whether the user has chosen to display their email or not.
            // If the data was submitted previously, the additional value that is
            // included in the request variables needs to be removed.
            if ($sale->getsaleLineItems()['elements'] !== []) {
                $items = $sale->getsaleLineItems()['elements'];
                $subtotal = 0;
                foreach($items as $item){
                    $price = $item->getPrice();
                    $subtotal = $subtotal + $price;
                };

                $form->get('saleAmount')->setData($subtotal);

            } else {
                $subtotal = 0;
                $form->get('saleAmount')->setData($subtotal);
            }
        });

        $builder->get('saleAmount')->addEventListener(
            FormEvents::POST_SUBMIT,
            function(FormEvent $event) {
                $form = $event->getForm();
                $subtotal = $event->getForm()->getData();
                $form->get('saleAmount')->setData($subtotal);
            }
        );

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sale::class
        ]);
    }
}