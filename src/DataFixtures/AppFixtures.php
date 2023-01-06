<?php

namespace App\DataFixtures;

use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{

    public function load(ObjectManager $manager): void
    {
        UserFactory::createOne([
            'username'=> 'SaleTest',
            'fullname'=> 'Test Test',
            'email'=> 'test@test.com',
            'roles'=>["ROLE_ADMIN", "ROLE_USER"]  
        ]);



        $manager->flush();
    }
}
