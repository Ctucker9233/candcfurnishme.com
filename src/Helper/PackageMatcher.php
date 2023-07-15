<?php

namespace App\Helper;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Psr\Log\LoggerInterface;

class PackageMatcher
{
    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly LoggerInterface $logger
    )
    {
        
    }

    public function matcher($search, $quantity, $price)
    {
        $cache = new PhpFilesAdapter(
            $namespace = "packages",
            $defaultLifetime = 0,
            // single file where values are cached
            $directory = $this->kernel->getProjectDir() . '/var/cache'
        );

        //dump($search);
        $packageCache = $cache->getItem('packages');
        //dump($vendorCache);
        $packages = $packageCache->get();
        //dump($customers);

        foreach($packages as $package)
        {
            foreach($package as $j){
                //dump($j);
                //dump($search);
                if(($search['description'] === $j['description'])){
                    if(($quantity === $j['pkgQuantity'])){
                        if(($search['packageId'] === $j['packageId'])){
                            if(($price == $j['price'])){
                                //dump('match');
                                return 'match';
                            }
                        }  
                    } 
                } 
                  
            }
        } 
    }
}