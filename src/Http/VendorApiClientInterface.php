<?php

namespace App\Http;

interface VendorApiClientInterface
{
    public function fetchVendors(string $vendors, string $tenant);
}