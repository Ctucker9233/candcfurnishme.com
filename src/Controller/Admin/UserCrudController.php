<?php
#Controller/Admin/UserCrudController.php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Sale;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController 
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordEncoder)
    {
    }

    public static function getEntityFqcn(): string {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User List')
            ->setEntityLabelInPlural('Users')
        ;
    }

    public function configureFields( string $pageName ): iterable {
        yield FormField::addPanel( 'User data' )->setIcon( 'fa fa-user' );
        yield IdField::new('id')->onlyOnIndex();
        yield textField::new('fullname');
        yield textField::new('username');
        yield EmailField::new( 'email' )->onlyWhenUpdating()->setDisabled();
        yield EmailField::new( 'email' )->onlyWhenCreating();
        yield EmailField::new( 'email' )->onlyOnIndex();
        $roles = [ 'ROLE_ADMIN', 'ROLE_MANAGER', 'ROLE_SALES' ];
        yield ChoiceField::new( 'roles' )
                         ->setChoices( array_combine( $roles, $roles ) )
                         ->allowMultipleChoices()
                         ->renderAsBadges();
        yield FormField::addPanel( 'Change password' )->setIcon( 'fa fa-key' );
        yield Field::new( 'password', 'New password' )->onlyWhenCreating()->setRequired( true )
                   ->setFormType( RepeatedType::class )
                   ->setFormTypeOptions( [
                       'type'            => PasswordType::class,
                       'first_options'   => [ 'label' => 'New password' ],
                       'second_options'  => [ 'label' => 'Repeat password' ],
                       'error_bubbling'  => true,
                       'invalid_message' => 'The password fields do not match.',
                   ] );
        yield Field::new( 'password', 'New password' )->onlyWhenUpdating()->setRequired( false )
                   ->setFormType( RepeatedType::class )
                   ->setFormTypeOptions( [
                       'type'            => PasswordType::class,
                       'first_options'   => [ 'label' => 'New password' ],
                       'second_options'  => [ 'label' => 'Repeat password' ],
                       'error_bubbling'  => true,
                       'invalid_message' => 'The password fields do not match.',
                   ] );
        yield AssociationField::new('SalesWritten')
            ->setCrudController(SaleCrudController::class)
            ->autocomplete();
    }
    
    public function createEditFormBuilder( EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context ): FormBuilderInterface {
        $plainPassword = $entityDto->getInstance()->getPassword();
        $formBuilder   = parent::createEditFormBuilder( $entityDto, $formOptions, $context );
        $this->addEncodePasswordEventListener( $formBuilder, $plainPassword );

        return $formBuilder;
    }

    public function createNewFormBuilder( EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context ): FormBuilderInterface {
        $formBuilder = parent::createNewFormBuilder( $entityDto, $formOptions, $context );
        $this->addEncodePasswordEventListener( $formBuilder );

        return $formBuilder;
    }

    protected function addEncodePasswordEventListener( FormBuilderInterface $formBuilder, $plainPassword = null ): void {
        $formBuilder->addEventListener( FormEvents::SUBMIT, function ( FormEvent $event ) use ( $plainPassword ) {
            /** @var User $user */
            $user = $event->getData();
            if ( $user->getPassword() !== $plainPassword ) {
                $user->setPassword( $this->passwordEncoder->hashPassword( $user, $user->getPassword() ) );
            }
        } );
    }
}