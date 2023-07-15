<?php

namespace App\Http;

interface PackageApiClientInterface
{
    public function fetchPackages(string $packages, string $tenant, $pid);
    
}
