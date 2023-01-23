<?php

namespace App\Controller\Admin;

use App\Entity\Sale;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class SaleCrudController extends AbstractCrudController
{
    /**
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_SALES')")
     */
    public static function getEntityFqcn(): string
    {
        return Sale::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
