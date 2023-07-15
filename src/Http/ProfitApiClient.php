<?php

namespace App\Http;

use App\Entity\Customer;
use App\Helper\CustomerProfileHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProfitApiClient implements ProfitApiClientInterface
{
    /**
     *@var customerQuery
     */

    private const baseUrl = 'https://70.166.12.16:8888/RESTWCFServiceLibrary/';
    private const serviceUrl = 'https://70.166.12.16:8888/RESTWCFServiceLibrary/?wsdl';

    public function __construct(private readonly HttpClientInterface $httpClient, private readonly EntityManagerInterface $entityManager, private readonly SerializerInterface $serializer)
    {
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
            ],
            'extra' => ['trace_content' => false]
        ]);
        //dump($response);
        //echo "CustomerProfile fetched.";
        //dump($response->getContent());
        //dump($response->getStatusCode());

        if($response->getStatusCode() !== 200){
            return new JsonResponse('Profit Api Client Error ', 400);
        };
        $contents = json_decode($response->getContent(), null, 512, JSON_THROW_ON_ERROR);
        //dump($contents);
        $result = CustomerProfileHelper::profileProcessor($contents);
        
        return new JsonResponse($result, 200);
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
            ],
            'extra' => ['trace_content' => false]
        ]);
        //dump($response);
        //echo "CustomerProfile fetched.";
        //dump($response->getContent());
        //dump($response->getStatusCode());

        if($response->getStatusCode() !== 200){
            return new JsonResponse('Profit Api Client Error ', 400);
        };

        $customer = json_decode($response->getContent(), null, 512, JSON_THROW_ON_ERROR);

        $result = CustomerProfileHelper::singleProfileProcessor($customer);
        
        return new JsonResponse($result, 200);
    }

    public function fetchIds($customerString, $Tenant): JsonResponse
    {
        $response = $this->httpClient->request('GET', self::baseUrl.$customerString.$Tenant, [
            'auth_basic' => ['WEBSALES', 'Ou81oDem!'],
            'capture_peer_cert_chain' => true,
            'verify_host' => false,
            'verify_peer' => false,
            'headers' => [
                'Accept' => 'application/json'
            ],
            'extra' => ['trace_content' => false],
            'timeout' => 60
        ]);
        //dump($response);
        //echo "CustomerProfile fetched.";
        //dump($response->getContent());
        //dump($response->getStatusCode());

        if($response->getStatusCode() !== 200 ){
            if($response->getStatusCode() === 404 ){
                $result = "no result";
                return new JsonResponse($result, 200);
            }
            return new JsonResponse('Profit Api Client Error ', 400);
        };

        $ids = json_decode($response->getContent(), null, 512, JSON_THROW_ON_ERROR);

        $result = CustomerProfileHelper::idProcessor($ids);
        
        return new JsonResponse($result, 200);
    }
}