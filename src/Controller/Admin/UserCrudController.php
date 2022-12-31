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
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use Twig\Environment;

class UserCrudController extends AbstractCrudController
{
    /**
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_SALES')")
     * @Route("/user", name="app_user")
     */
    /*private $adminUrlGenerator;

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator->set('users')->generateUrl();
    }*/

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    /*public function configureActions()*/

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setEntityLabelInSingular('User')
        ->setEntityLabelInPlural('Users')
        ->setPageTitle('index', '%entity_label_plural% List');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('username'),
            TextField::new('fullname', "Full Name"),
            EmailField::new('email'),
            TextField::new('password')->hideOnIndex(),
            ArrayField::new('roles', "Role"),
        ];
    }

    public function profile(user $user, AdminUrlGenerator $adminUrlGenerator)
    {
        $targetUrl = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Crud::PAGE_DETAIL)
            ->setEntityId($user->getId())
            ->generateUrl();
        return $this->redirect($targetUrl);
    }
}