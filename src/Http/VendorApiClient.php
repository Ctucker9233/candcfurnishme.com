<?php

namespace App\Http;

use App\Entity\Inventory;
use App\Entity\ItemLocation;
use App\Helper\VendorHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class VendorApiClient implements VendorApiClientInterface
{
    private const baseUrl = 'https://70.166.12.16:8888/RESTWCFServiceLibrary/';
    private const serviceUrl = 'https://70.166.12.16:8888/RESTWCFServiceLibrary/?wsdl';

    public function __construct
    (
        private readonly HttpClientInterface $httpClient, 
        private readonly EntityManagerInterface $entityManager, 
        private readonly SerializerInterface $serializer,
        private readonly VendorHelper $vendorHelper
        )
    {

    }

    public function fetchVendors($vendors, $Tenant): JsonResponse
    {
        $response = $this->httpClient->request('GET', self::baseUrl.$vendors.$Tenant, [
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

        $vend = json_decode($response->getContent(), null, 512, JSON_THROW_ON_ERROR);
        //dump($items);
        $vendArray = $this->vendorHelper->vendorProcessor($vend);
        //$data = $contentArray;
        return new JsonResponse($vendArray, 200);
    }
}