<?php

namespace App\Helper;

class InventoryHelper{
    public function inventoryProcessor($items){

        if (is_array($items) || is_object($items))
        {
            $itemsAsArray = array();
            foreach($items as $data){
                $webHide = self::webHider($data);
                //dump("web hide value");
                //dump($webHide);
                $price = 0;
            
                //dump($data->Prices[3]->Description);
                if(isset($data->Prices[3])){
                    $price = self::priceConverter($data->Prices[3]->Price);
                    //dump("Sale Price");
                    //dump($price);
                }
                else{
                    if(isset($data->Prices[1]->Price)){
                        $price = self::priceConverter($data->Prices[1]->Price);
                        //dump($price);
                    }
                    elseif(isset($data->Prices[0]->Price)){
                        //dump($data->Prices);
                        $price = self::priceConverter($data->Prices[0]->Price);
                    }
                    else{
                    $price = 0;
                    }
                }
                
                $itemArray = [
                'itemId' => $data->Id ?? "",
                'itemDescription' => $data->ItemDescription . " " . $data->ItemDescription2 ?? "",
                'mfcsku' => $data->MFCSKU ?? "",
                'price' => $price,
                'upcCode' => $data->UPCCode ?? "",
                'vendorId' => $data->Vendor->Id ?? "",
                'vendorName' => $data->Vendor->Name ?? "",
                'webHide' => $webHide,
                'webUrl' => $data->WebUrl ?? "",
                'backorderCode' => $data->BackOrderCode ?? "",
                'category' => $data->Category->Description ?? "",
                'isDeleted' => $data->IsDeleted ?? false,
                "isPackage" => $data->IsPackage ?? false,
                "discontinued" => false,
                ];

                array_push($itemsAsArray, $itemArray);     
            }           
             
            return $itemsAsArray;

        } 
        else{
            echo "It's not an object or array.";
        };
    }

    public function dbItemProcessor($data, $vendor){
        //dump($data);
        if($data->getVendorId() === $vendor){
            $itemsAsArray = [
                'itemId' => $data->getItemID(),
                'itemDescription' => $data->getItemDescription(),
                'mfcsku' => $data->getMfcsku(),
                'pictureLink' => $data->getPictureLink(),
                'price' => $data->getPrice(),
                'upcCode' => $data->getUpcCode(),
                'vendorId' => $data->getVendorId(),
                'vendorName' => $data->getVendorName(),
                'webHide' => $data->isWebHide(),
                'webUrl' => $data->getWebUrl(),
                'backorderCode' => $data->getBackorderCode(),
                'category' => $data->getCategory(),
                'quantity' => $data->getQuantity(),
                'isDeleted'=> $data->isIsDeleted(),
                'isPackage'=> $data->isIsPackage(),
                'BCItemId' => $data->getBCItemId(),
                'BCItemDescription' => $data->getBCItemDescription(),
                'BcVendorId' => $data->getBCVendorId()
                ];
                //dump($itemsAsArray);
                return $itemsAsArray;   
        }
    }
    public function dbBCItemProcessor($data){
        //dump($data);
            $itemsAsArray = [
                'itemId' => $data->getItemID(),
                'itemDescription' => $data->getItemDescription(),
                'mfcsku' => $data->getMfcsku(),
                'pictureLink' => $data->getPictureLink(),
                'price' => $data->getPrice(),
                'upcCode' => $data->getUpcCode(),
                'vendorId' => $data->getVendorId(),
                'vendorName' => $data->getVendorName(),
                'webHide' => $data->isWebHide(),
                'webUrl' => $data->getWebUrl(),
                'backorderCode' => $data->getBackorderCode(),
                'category' => $data->getCategory(),
                'quantity' => $data->getQuantity(),
                'isDeleted'=> $data->isIsDeleted(),
                'isPackage'=> $data->isIsPackage(),
                'BCItemId' => $data->getBCItemId(),
                'BCItemDescription' => $data->getBCItemDescription(),
                'BcVendorId' => $data->getBCVendorId(),
                'gtin' => $data->getGtin()
                ];
                //dump($itemsAsArray);
                return $itemsAsArray;   
    }

    public function dbItemIdProcessor($data, $vendor){
        //dump($data);
        $itemIds =[];
        foreach($data as $item){  
            if($item->getBackorderCode() === 'B' && $item->getVendorId() === $vendor){              
                $id = $item->getItemID();
                //dump($itemsAsArray);
                array_push($itemIds, $id);
            }
        }
        return $itemIds; 
    }

    public function locationByVendor($data){
        //dump($id);
        $inventoryArray = [];
        if (is_array($data) || is_object($data))
        {
            foreach($data as $i => $inventory) 
            {
                //dump($inventory);
                $location = [
                    'itemId' => $inventory->ItemId,
                    'quantity' => $inventory->Quantity,
                    'status' => $inventory->Status,
                    'location' => $inventory->Warehouse . " " . $inventory->Section . " " . $inventory->Unit . " " . $inventory->Bin
                ];
                array_push($inventoryArray, $location); 
    
            };        
        }
        else{
            echo "It's not an object or array.";
        };
        return $inventoryArray;
    }

    public function locationFilter($data, $id){
        //dump($id);
        $inventoryArray = [];
        
        if (is_array($data) || is_object($data))
        {
            foreach($data as $i) 
            {
                if($i !== 0){
                    if($i->itemId === $id){
                        //dump($i);
                        array_push($inventoryArray, $i);
                    } 
                }
                
            };        
        }
        else{
            echo "It's not an object or array.";
        };
        return $inventoryArray;
    }

    public function quantityCounter($data){
        $QOH = 0;
        if($data !== [] || isSet($data))
        {
            foreach($data as $quantity)
            {
                //dump($quantity);
                if(isSet($quantity) && ($quantity !== 0) && ($quantity !== []))
                {
                    if(is_array($quantity)){
                        if($quantity['status'] === "A")
                        {
                            $QOH = $QOH + $quantity->quantity;
                        }
                    }
                    else{
                        if($quantity->status === "A")
                        {
                            $QOH = $QOH + $quantity->quantity;
                        }
                    }
                    
                } 
            }
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

        if($data->WebHide === 1){
            //dump("hide on web");
            return true;
        }
        else{
            return false;
        }
    }

    public static function priceConverter($item){
        $price = $item * 100;
        //dump($price);
        return intval($price);
    }
}
    