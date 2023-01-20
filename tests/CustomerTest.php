<?php

namespace App\Tests;

use App\Entity\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CustomerTest extends DatabaseDependantTestCase
{
    /** @test **/
    public function create_customer_in_database()
    {
        //Setup

        $customer = new Customer();
        //name
        $customer->setName('Test, Test');
        //address1
        $customer->setAddress1('1234 Test Lane');
        //address2
        $customer->setAddress2('Unit 1');
        //city
        $customer->setCity('Santee');
        //state
        $customer->setState('CA');
        //postalcode
        $customer->setPostalcode('92071');
        //email
        $customer->setEmail('test@test.com');
        //phone1
        $customer->setPhone1('');
        //phone2
        $customer->setPhone2('');
        //phone3
        $customer->setPhone3('');
        //customerid
        $customer->setCustomerid('1234');

        $this->entityManager->persist($customer);

        $this->entityManager->flush();

        $customerRepository = $this->entityManager->getRepository(Customer::class);

        $customerRecord = $customerRepository->findOneBy(['name'=> 'Test, Test']);

        //Do something

        //Make assertions

        $this->assertEquals('Test, Test', $customerRecord->getName());
        $this->assertEquals('1234 Test Lane', $customerRecord->getAddress1());
        $this->assertEquals('Unit 1', $customerRecord->getAddress2());
        $this->assertEquals('Santee', $customerRecord->getCity());
        $this->assertEquals('CA', $customerRecord->getState());
        $this->assertEquals('92071', $customerRecord->getPostalcode());
        $this->assertEquals('test@test.com', $customerRecord->getEmail());
        $this->assertEquals('', $customerRecord->getPhone1());
        $this->assertEquals('', $customerRecord->getPhone2());
        $this->assertEquals('', $customerRecord->getPhone3());
        $this->assertEquals('1234', $customerRecord->getCustomerid());
    }
}