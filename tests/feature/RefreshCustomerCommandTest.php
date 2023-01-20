<?php

namespace App\Tests\feature;

use App\Entity\Customer;
use App\Http\FakeProfitApiClient;
use App\Tests\DatabaseDependantTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Components\HttpFoundation\JsonResponse;

class RefreshCustomerCommandTest extends DatabaseDependantTestCase
{
    /** @test */
    public function new_customer_command()
    {
        $application = new Application(self::$kernel);

        $command = $application->find('app:customer-refresh');

        $commandTester = new CommandTester($command);

        FakeProfitApiClient::$content = '{"name":"CASH CUSTOMER","address1":"","address2":"","city":"El Cajon","state":"CA","postalcode":"92020","email":"","phone1":"","phone2":"","phone3":"","customerid":"*CASH"}';

        $commandTester->execute([
            'Customers' => 'Customers',
            'Tenant' => '?Tenant=sm6apxdkrh',
        ]);

       
        $repo = $this->entityManager->getRepository(Customer::class);

        /** @var Customer $customer */
        $customerRecord = $repo->findOneBy(['name' => 'Test, Test']);
        dump($customerRecord);

        $this->assertSame('Test, Test', $customerRecord->getName());
        $this->assertSame('1234 Test Lane', $customerRecord->getAddress1());
        $this->assertSame('Unit 1', $customerRecord->getAddress2());
        $this->assertSame('Santee', $customerRecord->getCity());
        $this->assertSame('CA', $customerRecord->getState());
        $this->assertSame('92071', $customerRecord->getPostalcode());
        $this->assertSame('test@test.com', $customerRecord->getEmail());
        $this->assertSame('', $customerRecord->getPhone1());
        $this->assertSame('', $customerRecord->getPhone2());
        $this->assertSame('', $customerRecord->getPhone3());
        $this->assertSame('1234', $customerRecord->getCustomerid());
    }

    /** @test */
    public function update_customer_profile()
    {
        $customer = new customer();
        $customer->setName('Test, Test');
        $customer->setAddress1('1234 Test Lane');
        $customer->setAddress2('Unit 1');
        $customer->setCity('Santee');
        $customer->setState('CA');
        $customer->setPostalcode('92071');
        $customer->setEmail('test@test.com');
        $customer->setPhone1("");
        $customer->setPhone2("");
        $customer->setPhone3("");
        $customer->setCustomerid('1234');

        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        $customerId = $customer->getCustomerid();

        // SETUP //
        $application = new Application(self::$kernel);

        // Command
        $command = $application->find('app:customer-refresh');

        $commandTester = new CommandTester($command);

        // Non 200 response
        FakeProfitApiClient::$statusCode = 200;

        // Error content
        FakeProfitApiClient::$setContent([
            "name"=>"CASH CUSTOMER",
            "address1"=>"",
            "address2"=>""
            ]);

        dump(FakeProfitApiClient::$content);

        // DO SOMETHING
        $commandTester->execute([
            'Customers' => 'Customers',
            'Tenant' => '?Tenant=sm6apxdkrh',
        ]);

        $repo = $this->entityManager->getRepository(Customer::class);

        $customerCount = $repo->find('customerid');

        // MAKE ASSERTIONS
        $this->assertEquals("CASH CUSTOMER", $customerRecord->getName());
        $this->assertEquals("", $customerRecord->getAddress1());
        $this->assertEquals("", $customerRecord->getAddress2());
    
        $customerCount = $repo->createQueryBuilder('customer')
            ->select('count(customer.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // MAKE ASSERTIONS
        $this->assertEquals(0, $commandStatus);

        $this->assertEquals(0, $customerCount);
    }

    /** @test */
    public function non_200_Status()
    {
        // SETUP
        // SETUP //
        $application = new Application(self::$kernel);

        // Command
        $command = $application->find('app:customer-refresh');

        $commandTester = new CommandTester($command);

        // Non 200 response
        FakeProfitApiClient::$statusCode = 500;

        // Error content
        FakeProfitApiClient::$content = 'Finance API Client Error ';

        // DO SOMETHING
        $commandTester->execute([
            'Customers' => 'Customers',
            'Tenant' => '?Tenant=sm6apxdkrh',
        ]);

        $repo = $this->entityManager->getRepository(Customer::class);

        $customerCount = $repo->createQueryBuilder('customer')
            ->select('count(customer.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // MAKE ASSERTIONS
        $this->assertEquals(1, $commandStatus);

        $this->assertEquals(0, $customerCount);

        $this->assertStringContainsString('Finance API Client Error', $commandTester->getDisplay());
    }
}