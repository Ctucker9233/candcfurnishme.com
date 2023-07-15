<?php

namespace App\Helper;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Psr\Log\LoggerInterface;

class VendorMatcher
{
    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly LoggerInterface $logger
    )
    {
        
    }

    public function matcher($search)
    {
        $cache = new PhpFilesAdapter(
            $namespace = "vendor",
            $defaultLifetime = 0,
            // single file where values are cached
            $directory = $this->kernel->getProjectDir() . '/var/cache'
        );

        //dump($search);
        $vendorCache = $cache->getItem('vendors');
        //dump($vendorCache);
        $vendors = $vendorCache->get();
        //dump($customers);

        foreach($vendors as $index)
        {
            foreach($index as $j){
                //dump($j);
                if($search === $j){
                    return 'match';
                }  
            }
        } 
    }
}