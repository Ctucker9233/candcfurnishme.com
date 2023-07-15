<?php

namespace App\Form;

use App\Entity\Inventory;
use App\Entity\Sale;
use App\Repository\InventoryRepository;
use App\Form\InventoryType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\AssociationType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ArrayType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Form\DataTransformer\ItemToStringTransformer;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;

class ItemFormType extends AbstractType
{
    
    public function __construct(
        private ItemToStringTransformer $transformer,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->create('Items', FormType::class, ['inherit_data' => true]);
        foreach ($fields as $field) {
            $builder->get('Items')->add($field, TextType::class, []);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'compound' => true,
            'data_class' => Inventory::class
        ]);
    }
}