<?php

namespace App\Helper;

use Symfony\Component\Serializer\Encoder\JsonEncoder;

class PackageHelper{
    public function packageFilter($data){
        if (is_array($data) || is_object($data))
        {
            $array = json_decode(json_encode($data), true);
            $packageItems = [];
            
            while(isset($array)){
                //dump($array[0]);
                $filterBy = $array[0]['ParentId'];
                $filtered = array_filter($array, function ($arr) use ($filterBy){
                    return ($arr['ParentId'] == $filterBy);
                });
                //dump($filtered);
                $condensed = $this->packageProcessor($filtered);
                //dump($condensed);
                array_push($packageItems, $condensed);
                foreach($array as $arr => $key){
                    if($key['ParentId'] === $filterBy){
                        unset($array[$arr]);
                    }
                }
                $index = 0;
                if(count($array) > 0){
                    $array = array_combine(range($index, 
                        count($array) + ($index-1)),
                        array_values($array));
                    //dump($array);
                }
                else{
                    break;
                }
            }
            dump($packageItems);
            return $packageItems;    
        }
    }

    public function packageProcessor($data){
        if (is_array($data) || is_object($data))
        {
            $componentIds = [];
            $componentQuantity =[];
            foreach($data as $i => $item) 
            {
                array_push($componentIds, $item['CompId']);
                array_push($componentQuantity, $item['CompQtyPer']); 
            };
            $packagesAsArray = [
                'description' => $data[0]['ParentDesc'] . " " . $data[0]['ParentDesc2'],
                'componentIds' => $componentIds,
                'packageId' => $data[0]['ParentId'],
                'componentQuantity' => $componentQuantity,
                ];
                  
            return $packagesAsArray;   
        }
        else{
            echo "It's not an object or array.";
        };
    }

    public function dbPackageProcessor($data){
        $packageArray = [];
        if (is_array($data) || is_object($data))
        {
            foreach($data as $i => $item) 
            {
                //dump($item);
                
                $itemsAsArray = [
                'description' => $item->getDescription() ?? "",
                'componentIds' => $item->getComponentIds() ?? [],
                'pkgQuantity' => $item->getPkgQuantity() ?? 0,
                'packageId' => $item->getPackageId() ?? "",
                'componentQuantity' => $item->getcomponentQuantity() ?? 0,
                'price' => $item->getPrice()
                ];
                array_push($packageArray, $itemsAsArray);
            };  
            return $packageArray;   
        }
        else{
            echo "It's not an object or array.";
        };
    }

    public function dbBCPackageProcessor($data){;
                
        $itemsAsArray = [
            'description' => $data->getDescription(),
            'componentIds' => $data->getComponentIds(),
            'pkgQuantity' => $data->getPkgQuantity(),
            'packageId' => $data->getPackageId(),
            'componentQuantity' => $data->getcomponentQuantity(),
            'price' => $data->getPrice(),
            'BcPackDescription' => $data->getBcPackDescription(),
            'packPicture' => $data->getPackPicture(),
            'BcPackId' => $data->getBcPackId(),
            'active' => $data->isActive()
        ];
        return $itemsAsArray;   

    }

    public function dbPackageIdProcessor($data){
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

    public function pkgQuantity($components, $package){
        dump($components);
        if($components === []){
            return $quantity = 0;
        }
        $targetQty = $package->componentQuantity;
        
        dump($targetQty);
        $data = array();
        for($i=0; $i<count($targetQty); $i++){
            $count = 0;
            $compCount = $components[$i];
            //dump($compCount);
            for($j=$compCount; $j>0; $j--){
                if($compCount >= $targetQty[$i]){
                    $count = $count + 1;
                }
            }
            //dump($count);
            array_push($data, $count);
        }
        $quantity = min($data);
        //dump($quantity);
        return $quantity;
    }
    

    public static function webHider($data){
        if(!$data->WebHide === 0){
            return true;
        }
    }

    public static function priceConverter($item){
        $price = $item * 100;
        //dump($price);
        return $price;
    }
}
    