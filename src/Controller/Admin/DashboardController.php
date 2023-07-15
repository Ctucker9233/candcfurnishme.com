<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Sale;
use App\Entity\Customer;
use App\Entity\Inventory;
use App\Entity\Packages;
use App\Entity\ItemLocation;
use App\Entity\Vendors;
use App\Entity\Range;
use Cron\CronBundle\Entity\CronJob;
use App\Controller\Admin\UserCrudController;
use App\Controller\Admin\ProfileCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use Symfony\Component\Security\Core\User\UserInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Controller\DashboardControllerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController implements DashboardControllerInterface
{
    #[IsGranted('ROLE_SALES')]
    #[Route("/admin", name: "app_admin")]
    public function index(): Response
    {
        $routeBuilder = $this->container->get(AdminUrlGenerator::class);
        $url = $routeBuilder->setController(InventoryCrudController::class)->generateUrl();

        return $this->redirect($url);
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        if(!$user instanceof User) {
            throw new \Exception('Wrong user');
        }
        // Usually it's better to call the parent method because that gives you a
        // user menu with some menu items already created ("sign out", "exit impersonation", etc.)
        // if you prefer to create the user menu from scratch, use: return UserMenu::new()->...
        return parent::configureUserMenu($user)
            // use the given $user object to get the user name
            ->setName($user->getFullname())
            // you can return an URL with the avatar image)
            // use this method if you don't want to display the user image
            // you can also pass an email address to use gravatar's service
            ->setGravatarEmail($user->getEmail())

            // you can use any type of menu item, except submenus
            ->addMenuItems([
                MenuItem::linkToUrl('My Profile', 'fa fa-id-card', $this->container->get(AdminUrlGenerator::class)->setController(UserCrudController::class)->setAction(Action::DETAIL)->setEntityId($user->getId())->generateUrl()),
                MenuItem::section(),
                MenuItem::linkToLogout('Logout', 'fa fa-sign-out'),
            ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Tucker Solutions')
            ->setFaviconPath('images/favicon.ico');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
        yield MenuItem::linkToCrud('Users', 'fas fa-user', User::class)
            ->setPermission('ROLE_ADMIN');
        yield MenuItem::linkToCrud('Sales', 'fas fa-receipt', Sale::class);
        yield MenuItem::linkToCrud('Customers', 'fas fa-user-group', Customer::class);
        yield MenuItem::section('Inventory');
        yield MenuItem::linkToCrud('Items', 'fas fa-couch', Inventory::class);
        yield MenuItem::linkToCrud('Packages', 'fas fa-box', Packages::class);
        yield MenuItem::linkToCrud('Locations', 'fas fa-location-crosshairs', ItemLocation::class);
        yield MenuItem::section('Vendors');
        yield MenuItem::linkToCrud('Vendors', 'fas fa-wallet', Vendors::class);
        yield MenuItem::section('Tools')
            ->setPermission('ROLE_ADMIN');
        yield MenuItem::linkToCrud('Range', 'fas fa-screwdriver-wrench', Range::class)
            ->setPermission('ROLE_ADMIN');
    }

    public function configureActions(): Actions
    {
        return parent::configureActions()
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureAssets(): Assets
    {
        return parent::configureAssets()
            ->addCssFile('css/style.css');
    }

}
