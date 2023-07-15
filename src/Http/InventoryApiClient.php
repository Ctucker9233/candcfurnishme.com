<?php

namespace App\Http;

use App\Entity\Inventory;
use App\Entity\itemLocation;
use App\Helper\InventoryHelper;
use App\Helper\LocationHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class InventoryApiClient implements InventoryApiClientInterface
{
    
    /*private HttpClientInterface $httpClient;*/
    /*private EntityManagerInterface $entityManager;*/
    /*private SerializerInterface $serializer;*/
    /**
     *@var customerQuery
     */

    private const baseUrl = 'https://70.166.12.16:8888/RESTWCFServiceLibrary/';
    private const serviceUrl = 'https://70.166.12.16:8888/RESTWCFServiceLibrary/?wsdl';

    public function __construct
    (
        private readonly HttpClientInterface $httpClient, 
        private readonly EntityManagerInterface $entityManager, 
        private readonly SerializerInterface $serializer,
        private readonly InventoryHelper $inventoryHelper,
        private readonly LocationHelper $locationHelper
        )
    {

    }

    public function fetchItems($inventory, $Tenant, $id): JsonResponse
    {
        
        $response = $this->httpClient->request('GET', self::baseUrl.$inventory."/".$id.$Tenant, [
            'auth_basic' => ['WEBSALES', 'Ou81oDem!'],
            'capture_peer_cert_chain' => true,
            'verify_host' => false,
            'verify_peer' => false,
            'headers' => [
                'Accept' => 'application/json'
            ],
            'timeout' => 60,
        ]);

        if($response->getStatusCode() !== 200){
            if($response->getStatusCode() === 404 ){
                $result = "no result";
                return new JsonResponse($result, 200);
            }
            return new JsonResponse('Profit Api Client Error ', $response->getStatusCode());
        };

        $item = json_decode($response->getContent(), null, 512, JSON_THROW_ON_ERROR);
        //dump($items);
        $itemArray = $this->inventoryHelper->inventoryProcessor($item);
        //$data = $contentArray;
        return new JsonResponse($itemArray, 200);
    }

    public function fetchQuantity($inventory, $Tenant, $Vid): JsonResponse
    {
        $response = $this->httpClient->request('GET', self::baseUrl.$inventory."/".$Vid.$Tenant, [
            'auth_basic' => ['WEBSALES', 'Ou81oDem!'],
            'capture_peer_cert_chain' => true,
            'verify_host' => false,
            'verify_peer' => false,
            'headers' => [
                'Accept' => 'application/json'
            ],
            'timeout' => 60
        ]);

        if($response->getStatusCode() !== 200 && $response->getStatusCode() !== 404){
            return new JsonResponse('Profit Api Client Error ', $response->getStatusCode());
        }
        elseif($response->getStatusCode() === 404){
            $locationArray = [0];
            return new JsonResponse($locationArray, 200);
        }
        else{
           $inventories = json_decode($response->getContent(), null, 512, JSON_THROW_ON_ERROR);
            //dump($items);
            $locationArray = $this->inventoryHelper->locationByVendor($inventories);
            //$data = $contentArray;
            return new JsonResponse($locationArray, 200); 
        } 
    }

    public function fetchQuantityById($inventory, $Tenant): JsonResponse
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

       //dump($id);
        if($response->getStatusCode() !== 200){
            return new JsonResponse('Profit Api Client Error ', $response->getStatusCode());
        }

        $inventories = json_decode($response->getContent(), null, 512, JSON_THROW_ON_ERROR);
        
        $locationArray = $this->locationHelper->location($inventories);
        //dump($locationArray);
        //$data = $contentArray;
        return new JsonResponse($locationArray, 200); 
        
    }
}