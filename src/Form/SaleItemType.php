<?php

namespace App\Form;

use App\Entity\Sale;
use App\Entity\Inventory;
use App\Form\ItemType;
use App\Form\QuantityType;
use App\Repository\InventoryRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\AssociationType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ArrayType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class SaleItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        //dump($options);
        $builder
            ->add('quantity')
            ->add('item')
            ->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData'])
            ->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit'])
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPreSetData']);
    }

    public function onPreSetData(FormEvent $event) {
        $data = $event->getData();

        if ($data) {
            $data = unserialize($data);
            $event->setData($data);
        }
    }

    public function onPreSubmit(FormEvent $event) {
        $data = $event->getData();

        if ($data) {
            $data = serialize($data);
            $event->setData($data);
        }
    }


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection'    => false,
            'allow_extra_fields' => true
        ]);
    }
}