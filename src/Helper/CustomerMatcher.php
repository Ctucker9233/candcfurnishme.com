<?php

namespace App\Helper;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Psr\Log\LoggerInterface;

class CustomerMatcher
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
            $namespace = "customer",
            $defaultLifetime = 0,
            // single file where values are cached
            $directory = $this->kernel->getProjectDir() . '/var/cache'
        );

        $customerCache = $cache->getItem('customers');
        $customers = $customerCache->get();
        //dump($customers);

        foreach($customers as $i => $index)
        {
            foreach($index as $j){
                //dump($j);
                //dump($search);
                if($search === $j){;
                    return 'match';
                }
            }  
        } 
    }
}