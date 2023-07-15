<?php

namespace App\Helper;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Psr\Log\LoggerInterface;

class LocationMatcher
{
    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly LoggerInterface $logger
    )
    {
        
    }

    public function matcher($search, $cache)
    {
        if($search && $cache){
            if(($search->itemId === $cache['itemId']) &&
            ($search->quantity === $cache['quantity']) &&
            ($search->status === $cache['status']) &&
            ($search->location === $cache['location']))
            {
                return 'match';
            }  
        }
        else{
            return "no match";
        }    
         
    }

    public function locIds($search)
    {
        $cache = new PhpFilesAdapter(
            $namespace = "inventory",
            $defaultLifetime = 0,
            // single file where values are cached
            $directory = $this->kernel->getProjectDir() . '/var/cache'
        );

        //dump($search);
        $itemCache = $cache->getItem('locations');
        //dump($vendorCache);
        $items = $itemCache->get();
        //dump($customers);

        foreach($items as $item)
        {
            foreach($item as $j){
                $locationIds = [];
                //dump($j);
                if($search === $j['itemId'])
                {
                    array_push($locationIds, $j['id']);
                }
                return $locationIds;
            }
        } 
    }
}