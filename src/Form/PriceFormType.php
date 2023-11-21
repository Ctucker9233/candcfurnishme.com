<?php

namespace App\Form;

use App\Entity\Inventory;
use App\Form\ChoiceList\CustomChoiceLoader;
use App\StaticClass;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

class PriceFormType extends AbstractType
{
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($user): void {
            if (null !== $event->getData()->getItem()) {

                // we don't need to add the friend field because
                // the message will be addressed to a fixed friend
                return;
            }

            $form = $event->getForm();

            $formOptions = [
                'class' => Inventory::class,
                'choice_label' => 'price',
                'query_builder' => function (UserRepository $userRepository) use ($user): void {
                    // call a method on your repository that returns the query builder
                    // return $userRepository->createFriendsQueryBuilder($user);
                },
            ];

            // create the field, this is similar the $builder->add()
            // field name, field type, field options
            $form->add('price', EntityType::class, $formOptions);
        });

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Inventory::class,
        ]);
    }
}