<?php

namespace App\Helper;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Psr\Log\LoggerInterface;

class ItemMatcher
{
    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly LoggerInterface $logger
    )
    {
        
    }

    public function matcher($search, $QOH, $vendor, $id)
    {
        $cache = new PhpFilesAdapter(
            $namespace = "inventory",
            $defaultLifetime = 0,
            // single file where values are cached
            $directory = $this->kernel->getProjectDir() . '/var/cache'
        );
            
        $itemCache = $cache->getItem($id."_".$vendor);
        $item = $itemCache->get();
        //dump('search');
        //dump($search);
        //dump("item");
        //dump($item);
        if(!isset($item)){
            return 'not';
        }
        else{
            if($search->itemId === $item["itemId"] && 
                $search->itemDescription === $item['itemDescription'] && 
                $search->mfcsku === $item['mfcsku'] )
            {
            //dump($item['itemId']);
            //dump($item['price']);
            //dump($search->price);
            if(($search->price) === $item['price']){
                //dump($search['price']);
                //dump($item['price']);
                if($search->upcCode === $item['upcCode']){
                    //dump($search['upcCode']);
                    //dump($item['upcCode']);
                    if($search->vendorId === $item['vendorId']){
                        //dump($search['vendorId']);
                        //dump($item['vendorId']);
                        if($search->vendorName === $item['vendorName']){
                            //dump($search['vendorName']);
                            //dump($item['vendorName']);
                            if($search->webHide === $item['webHide']){
                                //dump($search['webHide']);
                                //dump($item['webHide']);
                                if($search->backorderCode === $item['backorderCode']){
                                    //dump($search['backorderCode']);
                                    //dump($item['backorderCode']);
                                    if($QOH === $item['quantity']){
                                        // dump($QOH);
                                        //dump($item['quantity']);
                                        if($search->isDeleted === $item['isDeleted']){
                                            if($search->isPackage === $item['isPackage']){
                                                //dump('made it');
                                                return 'match';
                                                                                                                    
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
        }    
        }
        
    }

    public function BCmatcher($item, $images){
        $cache = new PhpFilesAdapter(
            $namespace = "BCinventory",
            $defaultLifetime = 0,
            // single file where values are cached
            $directory = $this->kernel->getProjectDir() . '/var/cache'
        );
            
        $itemCache = $cache->getItem($item->sku);
        $BCitem = $itemCache->get();

        //dump($item);
        if(isset($BCitem['itemId'])){
            if($item->sku === $BCitem['itemId']){
                if($item->id === $BCitem['BCItemId']){
                    if($item->name === $BCitem['BCItemDescription']){
                        if(isset(json_decode($images->getContent())->data[0]->url_thumbnail) && json_decode($images->getContent())->data[0]->url_thumbnail === $BCitem['pictureLink']){
                            if($item->gtin === $BCitem['gtin']){
                                return "match";
                            }
                            else{
                                return($BCitem);
                            }
                        }
                        else{
                            return($BCitem);
                        }
                    }
                    else{
                        return($BCitem); 
                    }
                }
                return($BCitem);
            }
        }
        if(isset($BCitem['packageId'])){
            dump($BCitem['packageId']);
            //dump($item);
            if($item->name === $BCitem['BcPackDescription']){
                if(isset(json_decode($images->getContent())->data[0]->url_thumbnail) && json_decode($images->getContent())->data[0]->url_thumbnail === $BCitem['packPicture']){
                    if($item->id === $BCitem['BcPackId']){
                        return "match";
                    }
                    else{
                        return($BCitem);
                    }
                }
                else{
                    return($BCitem);
                }
            }
            else{
                return($BCitem);
            }
        }
        else{
            return($BCitem);
        }      
    } 
}
