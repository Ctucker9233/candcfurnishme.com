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
                $phones = self::phoneProcessor($customer);
                $newname = self::nameProcessor($customer->Name);
                
                $customerProfileAsArray = [
                'name' => $customer->Name,
                'firstname' => $newname[0],
                'lastname' => $newname[1],
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

            $name = $customer->Name;
            $name_parts = explode(", ", $name);
            $first_name = $name_parts[1];
            $last_name = $name_parts[0];

            $customerProfileAsArray = [
            'name' => $data->Name ?? "",
            'firstname' => $first_name,
            'lastname' => $last_name,
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

    public static function dbProfileProcessor($data){
        $contentArray = [];
        if (is_array($data) || is_object($data))
        {
            foreach($data as $customer) 
            {
                $customerProfileAsArray = [
                'name' => $customer->getName() ?? "",
                'firstname' => $customer->getFirstname() ?? "",
                'lastname' => $customer->getLastname() ?? "",
                'address1' => $customer->getAddress1() ?? "",
                'address2' => $customer->getAddress2() ?? "",
                'city' => $customer->getCity() ?? "",
                'state' => $customer->getState() ?? "",
                'postalcode' => $customer->getPostalcode() ?? "",
                'email' => $customer->getEmail() ?? "",
                'phone1' => $customer->getPhone1() ?? "",
                'phone2' => $customer->getPhone2() ?? "",
                'phone3' => $customer->getPhone3() ?? "",
                'customerid' => $customer->getCustomerid() ?? "",
                'isDeleted' => $customer->getIsDeleted() ?? false,
                ];
                array_push($contentArray, $customerProfileAsArray);
            };  
               
        }
        else{
            echo "It's not an object or array.";
        }
        return $contentArray;
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

    public static function nameProcessor($custname){
        $name = array();
        if (str_contains($custname, ', ')) {
            $name_parts = explode(", ", $custname);
            $first_name = $name_parts[1];
            $last_name = $name_parts[0];
            array_push($name, $first_name);
            array_push($name, $last_name);
        }
        elseif(str_contains($custname, ',')) {
            $name_parts = explode(",", $custname);
            $first_name = $name_parts[1];
            $last_name = $name_parts[0];
            array_push($name, $first_name);
            array_push($name, $last_name);
        }
        else{
            $first_name = $custname;
            $last_name = $custname;
            array_push($name, $first_name);
            array_push($name, $last_name);
        }
        //dump($name);
        return $name;
    }
}