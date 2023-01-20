<?php

namespace App\Http;

use App\Entity\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProfitApiClient implements ProfitApiClientInterface
{
    /**
     *@var HttpClientInterface
     */

    private HttpClientInterface $httpClient;

    /**
     *@var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    private SerializerInterface $serializer;
    /**
     *@var customerQuery
     */

    private const baseUrl = 'https://70.166.12.16:8888/RESTWCFServiceLibrary/';
    private const serviceUrl = 'https://70.166.12.16:8888/RESTWCFServiceLibrary/?wsdl';

    public function __construct(HttpClientInterface $httpClient, $ApiKey, EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    public function fetchProfileIds($customerString, $Tenant): JsonResponse
    {
        $response = $this->httpClient->request('GET', self::baseUrl.$customerString.$Tenant, [
            'auth_basic' => ['WEBSALES', 'Ou81oDem!'],
            'capture_peer_cert_chain' => true,
            'verify_host' => false,
            'verify_peer' => false,
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);

        if($response->getStatusCode() !== 200){
            return new JsonResponse('Profit Api Client Error ', 400);
        };

        $contents = json_decode($response->getContent());
        $idArray = [];
        if (is_array($contents) || is_object($contents))
        {
            foreach($contents as $i => $customer) 
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
        $data = $idArray;
        return new JsonResponse($data, 200);
    }

    public function fetchCustomerProfile($customerString, $Tenant): JsonResponse
    {
        $response = $this->httpClient->request('GET', self::baseUrl.$customerString.$Tenant, [
            'auth_basic' => ['WEBSALES', 'Ou81oDem!'],
            'capture_peer_cert_chain' => true,
            'verify_host' => false,
            'verify_peer' => false,
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);
        //dump($response);
        //echo "CustomerProfile fetched.";
        //dump($response->getContent());
        //dump($response->getStatusCode());

        if($response->getStatusCode() !== 200){
            return new JsonResponse('Profit Api Client Error ', 400);
        };

        $contents = json_decode($response->getContent());
        //dump($contents);
        $contentArray = [];
        if (is_array($contents) || is_object($contents))
        {
            foreach($contents as $i => $customer) 
            {
                //dump($customer);
                //dump($customer->Phones);
                $phone1 = "";
                $phone2 = "";
                $phone3 = "";
                for($i=0; $i<=2; $i++)
                {
                    if(isset($customer->Phones))
                    {
                        //echo "Phone object";
                        //dump($customer->Phones);
                        //echo "not empty";
                        if(isset($customer->Phones[$i]))
                        {
                            //dump($customer->Phones[$i]->Number);
                            //echo "phones";
                            if(isset($customer->Phones[$i]->Number))
                            {
                                if($i===0){
                                    $phone1 = $customer->Phones[$i]->Number;
                                    //dump($phone1);
                                }
                                elseif($i===1){
                                    $phone2 = $customer->Phones[$i]->Number;
                                }
                                elseif($i===3){
                                    $phone3 = $customer->Phones[$i]->Number;
                                }
                                else{
                                    $phone1 = "";
                                    $phone2 = "";
                                    $phone3 = ""; 
                                };
                            };
                        };
                    };
                };
                $customerProfileAsArray = [
                'name' => $customer->Name ?? "",
                'address1' => $customer->Address1 ?? "",
                'address2' => $customer->Address2 ?? "",
                'city' => $customer->City ?? "",
                'state' => $customer->State ?? "",
                'postalcode' => $customer->PostalCode ?? "",
                'email' => $customer->EMail ?? "",
                'phone1' => $phone1 ?? "",
                'phone2' => $phone2 ?? "",
                'phone3' => $phone3 ?? "",
                'customerid' => $customer->Id ?? "",
                'isDeleted' => $customer->IsDeleted ?? false,
                ];
                array_push($contentArray, $customerProfileAsArray);
            };  
               
        }
        else{
            echo "It's not an object or array.";
        };
        //$data = $contentArray;
        return new JsonResponse($contentArray, 200);
    }

    public function fetchSingleProfile($customerString, $Tenant, $id): JsonResponse
    {
        $response = $this->httpClient->request('GET', self::baseUrl.$customerString."/".$id.$Tenant, [
            'auth_basic' => ['WEBSALES', 'Ou81oDem!'],
            'capture_peer_cert_chain' => true,
            'verify_host' => false,
            'verify_peer' => false,
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);
        //dump($response);
        //echo "CustomerProfile fetched.";
        //dump($response->getContent());
        //dump($response->getStatusCode());

        if($response->getStatusCode() !== 200){
            return new JsonResponse('Profit Api Client Error ', 400);
        };

        $customer = json_decode($response->getContent());
        $contentArray = [];
        if (is_array($customer) || is_object($customer))
        {
            $phone1 = "";
            $phone2 = "";
            $phone3 = "";
            for($i=0; $i<=2; $i++)
            {
                if(isset($customer->Phones))
                {
                    //echo "Phone object";
                    //dump($customer->Phones);
                    //echo "not empty";
                    if(isset($customer->Phones[$i]))
                    {
                        //dump($customer->Phones[$i]->Number);
                        //echo "phones";
                        if(isset($customer->Phones[$i]->Number))
                        {
                            if($i===0){
                                $phone1 = $customer->Phones[$i]->Number;
                                //dump($phone1);
                            }
                            elseif($i===1){
                                $phone2 = $customer->Phones[$i]->Number;
                            }
                            elseif($i===3){
                                $phone3 = $customer->Phones[$i]->Number;
                            }
                            else{
                                $phone1 = "";
                                $phone2 = "";
                                $phone3 = ""; 
                            };
                        };
                    };
                };
            };
            $customerProfileAsArray = [
            'name' => $customer->Name ?? "",
            'address1' => $customer->Address1 ?? "",
            'address2' => $customer->Address2 ?? "",
            'city' => $customer->City ?? "",
            'state' => $customer->State ?? "",
            'postalcode' => $customer->PostalCode ?? "",
            'email' => $customer->EMail ?? "",
            'phone1' => $phone1 ?? "",
            'phone2' => $phone2 ?? "",
            'phone3' => $phone3 ?? "",
            'customerid' => $customer->Id ?? "",
            'isDeleted' => $customer->IsDeleted ?? false,
            ];
            array_push($contentArray, $customerProfileAsArray);
            
        }
        else{
            echo "It's not an object or array.";
        };
        return new JsonResponse($contentArray, 200);
    }
}