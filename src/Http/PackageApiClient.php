<?php

namespace App\Http;

use App\Entity\Inventory;
use App\Entity\itemLocation;
use App\Entity\Packages;
use App\Helper\PackageHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class PackageApiClient implements PackageApiClientInterface
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
        private readonly PackageHelper $packageHelper
        )
    {

    }

    public function fetchPackageItems($packages, $Tenant): JsonResponse
    {
        $response = $this->httpClient->request('GET', self::baseUrl.$packages.$Tenant, [
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

        $items = json_decode($response->getContent(), null, 512, JSON_THROW_ON_ERROR);
        //dump($items);
        $itemArray = $this->packageHelper->packageFilter($items);
        //$data = $contentArray;
        return new JsonResponse($itemArray, 200);
    }

    public function fetchPackages($packages, $Tenant, $pid): JsonResponse
    {
        $response = $this->httpClient->request('GET', self::baseUrl.$packages."/".$pid.$Tenant, [
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

        $packages = json_decode($response->getContent(), null, 512, JSON_THROW_ON_ERROR);
        //dump($items);
        $packageArray = $this->packageHelper->packageProcessor($packages);
        //$data = $contentArray;
        return new JsonResponse($packageArray, 200);
    }

    public function fetchPackageItem($inventory, $Tenant, $id): JsonResponse
    {
        $response = $this->httpClient->request('GET', self::baseUrl.$inventory."/".$id.$Tenant, [
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

        $item = json_decode($response->getContent(), null, 512, JSON_THROW_ON_ERROR);
        //dump($items);
        //$data = $contentArray;
        return new JsonResponse($item, 200);
    }

    public function fetchQuantity($products, $Tenant, $id): JsonResponse
    {
        $response = $this->httpClient->request('GET', self::baseUrl.$products."/".$id.$Tenant, [
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
            $locationArray = $this->inventoryHelper->locationByVendor($inventories, $id);
            //$data = $contentArray;
            return new JsonResponse($locationArray, 200); 
        } 
    }
}