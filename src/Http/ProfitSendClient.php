<?php

namespace App\Http;

use App\Entity\Customer;
use App\Helper\CustomerProfileHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class ProfitSendClient implements ProfitSendClientInterface
{
    /**
     *@var customerQuery
     */

    private const baseUrl = 'https://70.166.12.16:8888/RESTWCFServiceLibrary/';
    private const serviceUrl = 'https://70.166.12.16:8888/RESTWCFServiceLibrary/?wsdl';

    public function __construct(private readonly HttpClientInterface $httpClient, private readonly EntityManagerInterface $entityManager, private readonly SerializerInterface $serializer)
    {
    }

    public function sendProfitOrder($Tenant, $body): JsonResponse
    {
        $response = $this->httpClient->request('POST', self::baseUrl.'Fulfillments'.$Tenant, [
            'auth_basic' => ['WEBSALES', 'Ou81oDem!'],
            'verify_host' => false,
            'verify_peer' => false,
            'headers' => [
                'Accept' => '*/*',
                'Content_Type' => 'application/json'
            ],
            'timeout' => 180,
            'json' => json_decode($body, true)
        ]);

        dump($body);

        if($response->getStatusCode() !== 200){
            return new JsonResponse('Profit Api Client Error ', 400);
        };
        
        return new JsonResponse('Success', 200);
    }

}