<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Twig\Environment;

class UserCrudController extends AbstractCrudController
{
    /**
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_USER')")
     * @Route("/user", name="app_user")
     */

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setEntityLabelInSingular('User')
        ->setEntityLabelInPlural('Users')
        ->setPageTitle('index', '%entity_label_plural% List');
    }
    
    public function configureActions(Actions $actions): Actions
    {
        dump($actions);
        $editProfile = Action::new('editProfile', 'Edit Profile', 'fas fa-file-invoice');
        $editProfile->linkToUrl(function($entity) {
        $adminUrlGenerator = $this->get(AdminUrlGenerator::class);
        $url = $adminUrlGenerator
            ->setController(UserCrudController::class)
            ->setAction(Action::EDIT)
            ->set('filters', [
                'agent' => [
                    'comparison' => '=',
                    'value' => $entity->getId()
                ]
            ])
            ->generateUrl();
        return $url;
        });

        $actions->add(Crud::PAGE_EDIT, $editProfile);

        return $actions
            ->setPermission(Action::NEW, 'ROLE_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
            /*->add(Action::EDIT, $editProfile);*/
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            yield IdField::new('id')
                ->hideOnForm(),
            yield TextField::new('username'),
            yield TextField::new('fullname', "Full Name"),
            yield EmailField::new('email'),
            yield TextField::new('password')->hideOnIndex(),
            $roles = ['ROLE_ADMIN', 'ROLE_MANAGER', 'ROLE_USER'],
            yield ChoiceField::new('roles')
                ->setChoices(array_combine($roles, $roles))
                ->allowMultipleChoices()
                ->renderExpanded()
                ->renderAsBadges()
        ];
    }
}