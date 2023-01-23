<?php

namespace App\Http;

use App\Entity\Inventory;
use App\Entity\ItemLocation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class InventoryApiClient implements InventoryApiClientInterface
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

    public function __construct(HttpClientInterface $httpClient, EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;

    }

    public function fetchItems($inventory, $Tenant): JsonResponse
    {
        $response = $this->httpClient->request('GET', self::baseUrl.$inventory.$Tenant, [
            'auth_basic' => ['WEBSALES', 'Ou81oDem!'],
            'capture_peer_cert_chain' => true,
            'verify_host' => false,
            'verify_peer' => false,
            'headers' => [
                'Accept' => 'application/json'
            ],
            'timeout' => 60
        ]);

        if($response->getStatusCode() !== 200){
            return new JsonResponse('Profit Api Client Error ', $response->getStatusCode());
        };

        $items = json_decode($response->getContent());
        //dump($items);
        $itemArray = [];
        if (is_array($items) || is_object($items))
        {
            foreach($items as $i => $item) 
            {
                //dump($item);
                
                $itemsAsArray = [
                'itemId' => $item->Id ?? "",
                'itemDescription' => $item->ItemDescription . " " . $item->ItemDescription2 ?? "",
                'mfcsku' => $item->MFCSKU ?? "",
                'pictureLink' => $item->PictureName ?? "",
                'price' => $item->Prices[1]->Price ?? "",
                'upcCode' => $item->UPCCode ?? "",
                'vendorId' => $item->Vendor->Id ?? "",
                'vendorName' => $item->Vendor->Name ?? "",
                'webHide' => $item->WebHide ?? false,
                'webUrl' => $item->WebUrl ?? "",
                'backorderCode' => $item->BackOrderCode ?? "",
                'category' => $item->Category->Description ?? "",
                ];
                array_push($itemArray, $itemsAsArray);
            };  
               
        }
        else{
            echo "It's not an object or array.";
        };
        //$data = $contentArray;
        return new JsonResponse($itemArray, 200);
    }

    public function fetchQuantity($inventory, $Tenant, $Id): JsonResponse
    {
        $response = $this->httpClient->request('GET', self::baseUrl.$inventory.$Tenant, [
            'auth_basic' => ['WEBSALES', 'Ou81oDem!'],
            'capture_peer_cert_chain' => true,
            'verify_host' => false,
            'verify_peer' => false,
            'headers' => [
                'Accept' => 'application/json'
            ],
            'timeout' => 60
        ]);

        if($response->getStatusCode() !== 200){
            return new JsonResponse('Profit Api Client Error ', $response->getStatusCode());
        };

        $inventories = json_decode($response->getContent());
        //dump($items);
        $inventoryArray = [];
        if (is_array($inventories) || is_object($inventories))
        {
            foreach($inventories as $i => $inventory) 
            {
                //dump($inventory);
                
                //array_push($itemArray, $itemsAsArray);
            };  
               
        }
        else{
            echo "It's not an object or array.";
        };
        //$data = $contentArray;
        return new JsonResponse($itemArray, 200);
    }
}