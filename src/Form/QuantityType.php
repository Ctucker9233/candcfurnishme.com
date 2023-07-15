<?php

namespace App\Form;

use App\Entity\Sale;
use App\Entity\SaleLineItems;
use App\Entity\Inventory;
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

    public function __construct(private EntityManagerInterface $entityManager)
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
    }

    

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SaleLineItems::class,
            'compound' => true
        ]);
    }
}