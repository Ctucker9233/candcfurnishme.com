<?php

namespace App\Helper;

class CustomerProfileHelper
{
    public static function idProcessor($data){
        $idArray = [];
        if (is_array($data) || is_object($data))
        {
            foreach($data as $i => $customer) 
            {
                $customerIdAsArray = [
                    'customerid' => $customer->Id ?? "",
                ];
                array_push($idArray, $customerIdAsArray);
            };
        }
        else{
            echo "It's not an object or array.";
        };
        return $idArray;
    }

    public static function profileProcessor($data){
        $contentArray = [];
        if (is_array($data) || is_object($data))
        {
            foreach($data as $i => $customer) 
            {
                //dump($customer);
                //dump($customer->Phones);
                $phones = self::phoneProcessor($data);
                
                $customerProfileAsArray = [
                'name' => $customer->Name ?? "",
                'address1' => $customer->Address1 ?? "",
                'address2' => $customer->Address2 ?? "",
                'city' => $customer->City ?? "",
                'state' => $customer->State ?? "",
                'postalcode' => $customer->PostalCode ?? "",
                'email' => $customer->EMail ?? "",
                'phone1' => $phones[0] ?? "",
                'phone2' => $phones[1] ?? "",
                'phone3' => $phones[2] ?? "",
                'customerid' => $customer->Id ?? "",
                'isDeleted' => $customer->IsDeleted ?? false,
                ];
                array_push($contentArray, $customerProfileAsArray);
            };  
               
        }
        else{
            echo "It's not an object or array.";
        }
        return $contentArray;
    }

    public static function singleProfileProcessor($data){
        $contentArray = [];
        if (is_array($data) || is_object($data))
        {
            $phones = self::phoneProcessor($data);

            $customerProfileAsArray = [
            'name' => $data->Name ?? "",
            'address1' => $data->Address1 ?? "",
            'address2' => $data->Address2 ?? "",
            'city' => $data->City ?? "",
            'state' => $data->State ?? "",
            'postalcode' => $data->PostalCode ?? "",
            'email' => $data->EMail ?? "",
            'phone1' => $phones[0] ?? "",
            'phone2' => $phones[1] ?? "",
            'phone3' => $phones[2] ?? "",
            'customerid' => $data->Id ?? "",
            'isDeleted' => $data->IsDeleted ?? false,
            ];
            array_push($contentArray, $customerProfileAsArray);
            
        }
        else{
            echo "It's not an object or array.";
        };
    }

    public static function phoneProcessor($data){
        $phones=[];
        for($i=0; $i<=2; $i++)
        {
            if(isset($data->Phones))
            {
                //echo "Phone object";
                //dump($customer->Phones);
                //echo "not empty";
                if(isset($data->Phones[$i]))
                {
                    //dump($customer->Phones[$i]->Number);
                    //echo "phones";
                    if(isset($data->Phones[$i]->Number))
                    {
                        if($i===0){
                            $phone1 = $data->Phones[$i]->Number;
                            array_push($phones, $phone1);
                        }
                        elseif($i===1){
                            $phone2 = $data->Phones[$i]->Number;
                            array_push($phones, $phone2);
                        }
                        elseif($i===3){
                            $phone3 = $data->Phones[$i]->Number;
                            array_push($phones, $phone3);
                        }
                        else{
                            array_push($phones, "");
                        };
                    }
                    else{
                        array_push($phones, "");
                    }
                }
                else{
                    array_push($phones, "");
                }
            }
            else{
                 array_push($phones, "");
            }
        };
        return $phones;
    }
}