<?php

namespace App\Helper;

class VendorHelper{
    public function vendorProcessor($data){
        $vendorArray = [];
        if (is_array($data) || is_object($data))
        {
            foreach($data as $i => $item) 
            {
                //dump($item);
                
                $vendorsAsArray = [
                'vendorId' => $item->Id ?? "",
                'vendorName' => $item->Name ?? "",
                'address1' => $item->Address1 ?? "",
                'address2' => $item->Address2 ?? "",
                'city' => $item->City?? "",
                'rep' => $item->Contact ?? "",
                'email' => $item->EMail ?? "",
                'phone' => $item->PhoneNumber ?? "",
                'postalCode' => $item->PostalCode ?? "",
                'state' => $item->State ?? "",
                'command' => 'app:specific-vendor-update '.$item->Id
                ];
                array_push($vendorArray, $vendorsAsArray);
            }; 
        //dump($vendorArray);
        return $vendorArray;       
        }
        else{
            echo "It's not an object or array.";
        };
    }

    public function dbVendorProcessor($data){
        $vendorArray = [];
        if (is_array($data) || is_object($data))
        {
            foreach($data as $i => $item) 
            {
                //dump($item);
                
                $vendorsAsArray = [
                'vendorId' => $item->getVendorId() ?? "",
                'vendorName' => $item->getVendorName() ?? "",
                'address1' => $item->getAddress1() ?? "",
                'address2' => $item->getAddress2() ?? "",
                'city' => $item->getCity() ?? "",
                'rep' => $item->getRep() ?? "",
                'email' => $item->getEmail() ?? "",
                'phone' => $item->getPhone() ?? "",
                'postalCode' => $item->getPostalCode() ?? "",
                'state' => $item->getState() ?? "",
                'active' => $item->isActive(),
                'command' => 'app:specific-vendor-update '.$item->getVendorId(),
                ];
                array_push($vendorArray, $vendorsAsArray);
            }; 
        //dump($vendorArray);
        return $vendorArray;       
        }
        else{
            echo "It's not an object or array.";
        };
    }

    public function dbVendorIdProcessor($data){
        $vendorIdArray = [];
        if (is_array($data) || is_object($data))
        {
            foreach($data as $i => $item) 
            {
                //dump($item);
                
                $vendorId = $item->getVendorId() ?? "";
                
                array_push($vendorIdArray, $vendorId);
            }; 
        //dump($vendorArray);
        return $vendorIdArray;       
        }
        else{
            echo "It's not an object or array.";
        };
    }
}