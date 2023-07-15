<?php

namespace App\Helper;

use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\HttpKernel\KernelInterface;

class LocationHelper{

    public function location($data){
        //dump($id);
        $inventoryArray = [];
        if (is_array($data) || is_object($data))
        {
            //dump($data);
            foreach($data as $i => $inventory) 
            {
                $location = [
                    'itemId' => $inventory->ItemId,
                    'quantity' => $inventory->Quantity,
                    'status' => $inventory->Status,
                    'location' => $inventory->Warehouse . " " . $inventory->Section . " " . $inventory->Unit . " " . $inventory->Bin
                ];
                //dump($location);
                array_push($inventoryArray, $location);
                //dump($inventoryArray); 
            }
            return $inventoryArray;       
        }
        else{
            echo "It's not an object or array.";
        };
    }

    public function locationById($data, $id){
        //dump($id);
        $inventoryArray = [];
        if (is_array($data) || is_object($data))
        {
            //dump($data);
            foreach($data as $i => $inventory) 
            {
                if($inventory->itemId === $id){
                    array_push($inventoryArray, $inventory);
                } 
            }
            return $inventoryArray;       
        }
        else{
            echo "It's not an object or array.";
        };
    }

    public function cachedById($data, $id){
        $inventoryArray = [];
        foreach($data as $locs) 
        {
            foreach($locs as $loc){
                if($loc['itemId'] === $id){
                    array_push($inventoryArray, $loc); 
                }
            }        
        }
        return $inventoryArray;
    }

    public function dbLocation($data){
        $locationArray = [];
        //dump($id);
        foreach($data as $item){
            
            $location = [
                'id' => $item->getId(),
                'itemId' => $item->getItemId(),
                'quantity' => $item->getQuantity(),
                'status' => $item->getStatus(),
                'location' => $item->getLocation()
            ];   
            array_push($locationArray, $location); 
        }
        
        return $locationArray;
    }

    public function quantityCounter($data){
        $QOH = 0;
        if($data !== [] || !isSet($data))
        {
            foreach($data as $quantity)
            {
                //dump($quantity);
                if(isSet($quantity) && ($quantity !== 0) && ($quantity !== []))
                {
                    if($quantity->status === "A")
                    {
                        $QOH = $QOH + $quantity->quantity;
                    }
                }
                else
                {
                    echo "none available";
                } 
            }
        }
        else
        {
            echo "none available";
        }
        //dump("Quantity will be set to " . $QOH);
        return $QOH;
    }

    public function dbPackageIdProcessor($value){
        $pkgIdArray = [];
        if (is_array($data) || is_object($data))
        {
            foreach($data as $i => $item) 
            {
                //dump($item);
                if ($item->isIsPackage() === true){
                    array_push($pkgIdArray, $item->getItemId());
                }  
            };  
            return $pkgIdArray;   
        }
        else{
            echo "It's not an object or array.";
        };
    }

    public static function webHider($data){
        if(!$data->WebHide === 0){
            return true;
        }
    }
}
    