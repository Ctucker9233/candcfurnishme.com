<?php

namespace App\Tests\integration;

use App\Tests\DatabaseDependantTestCase;

class ProfitApiClientTest extends DatabaseDependantTestCase
{
    /**
     *@test
     *@group integration
     */
     
    public function getGoodData()
    {
        $ProfitApiClient = self::$kernel->getcontainer()->get('profit-api-client');

        $response = $ProfitApiClient->fetchCustomerProfile('Customers', '?Tenant=sm6apxdkrh');

        $customerProfile = json_decode($response->getContent());
        dump($customerProfile);
        for($i=0; $i<count($customerProfile); $i++){
            //dump($response);
            dump($customerProfile[$i]);
        echo "Still in ProfitApiClientTest";

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame('Test, Test', $customerProfile[$i]->name);
        $this->assertSame('1234 Test Lane', $customerProfile[$i]->address1);
        $this->assertSame('Unit 1', $customerProfile[$i]->address2);
        $this->assertSame('Santee', $customerProfile[$i]->city);
        $this->assertSame('CA', $customerProfile[$i]->state);
        $this->assertSame('92071', $customerProfile[$i]->postalcode);
        $this->assertSame('test@test.com', $customerProfile[$i]->email);
        $this->assertSame('', $customerProfile[$i]->phone1);
        $this->assertSame('', $customerProfile[$i]->phone2);
        $this->assertSame('', $customerProfile[$i]->phone3);
        $this->assertSame('1234', $customerProfile[$i]->customerid);
        }   
    }
}