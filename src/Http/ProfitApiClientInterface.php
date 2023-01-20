<?php

namespace App\Http;

interface ProfitApiClientInterface
{
    public function fetchCustomerProfile(string $customer, string $tenant);
}