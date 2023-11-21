<?php

namespace App\Http\Bigcommerce;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class Client
{
    private const version3 = '/v3';
    private const version2 = '/v2';
    private $hash;
    private $clientId;
    private $accessToken;

    public function __construct
    (
        private readonly HttpClientInterface $client,  
        private readonly SerializerInterface $serializer,
        string $hash,
        string $clientId,
        string $accessToken
        )
    {
        $this->hash = $hash;
        $this->clientId = $clientId;
        $this->accessToken = $accessToken;
    }
    public function clientGet($request, $filter): JsonResponse
    {
        $response = $this->client->request('GET', $this->hash.self::version3.$request."?".$filter, [
            'verify_peer' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Auth-Token' => $this->accessToken
            ],
            'timeout' => 6000
        ]);

        if($response->getStatusCode() !== 200){
            return new JsonResponse('Bigcommerce error ', $response->getStatusCode());
        };
        $vendors = $response->getContent();
        //dump(json_decode($vendors));
        return new JsonResponse(json_decode($vendors), 200);
    }

    public function clientGetOrder($request, $order): JsonResponse
    {
        $response = $this->client->request('GET', $this->hash.self::version2.$request."?"."min_id=".$order, [
            'verify_peer' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Auth-Token' => $this->accessToken
            ],
            'timeout' => 6000
        ]);

        if($response->getStatusCode() !== 200){
            return new JsonResponse('Bigcommerce error ', $response->getStatusCode());
        };
        $vendors = $response->getContent();
        //dump(json_decode($vendors));
        return new JsonResponse(json_decode($vendors), 200);
    }

    public function clientGetOrderItems($request): JsonResponse
    {
        $response = $this->client->request('GET', $this->hash.self::version2.$request, [
            'verify_peer' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Auth-Token' => $this->accessToken
            ]
        ]);

        if($response->getStatusCode() !== 200){
            return new JsonResponse('Bigcommerce error ', $response->getStatusCode());
        };
        $vendors = $response->getContent();
        //dump(json_decode($vendors));
        return new JsonResponse(json_decode($vendors), 200);
    }

    public function clientGetShipping($request): JsonResponse
    {
        $response = $this->client->request('GET', $this->hash.self::version2.$request, [
            'verify_peer' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Auth-Token' => $this->accessToken
            ],
            'timeout' => 6000
        ]);

        if($response->getStatusCode() !== 200){
            return new JsonResponse('Bigcommerce error ', $response->getStatusCode());
        };
        $vendors = $response->getContent();
        //dump(json_decode($vendors));
        return new JsonResponse(json_decode($vendors), 200);
    }

    public function clientGetImage($request, $id, $tail): JsonResponse
    {
        $response = $this->client->request('GET', $this->hash.self::version3.$request."/".$id.$tail, [
            'verify_peer' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Auth-Token' => $this->accessToken
            ],
            'timeout' => 6000
        ]);

        if($response->getStatusCode() !== 200){
            return new JsonResponse('Bigcommerce error ', $response->getStatusCode());
        };
        $vendors = $response->getContent();
        //dump(json_decode($vendors));
        return new JsonResponse(json_decode($vendors), 200);
    }

    public function clientPost($request, $body): JsonResponse
    {
        $response = $this->client->request('POST', $this->hash.self::version3.$request, [
            'verify_peer' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Auth-Token' => $this->accessToken
            ],
            'timeout' => 6000,
            'body' => $body
        ]);

        if($response->getStatusCode() !== 200){
            dump($response);
            return new JsonResponse('Bigcommerce error ', $response->getStatusCode());
        };
        $vendors = $response->getContent();
        //dump(json_decode($vendors));
        return new JsonResponse(json_decode($vendors), 200);
    }

    public function clientPut($request, $id, $body): JsonResponse
    {
        $response = $this->client->request('PUT', $this->hash.self::version3.$request."/".$id, [
            'verify_peer' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Auth-Token' => $this->accessToken
            ],
            'body' => $body,
            'timeout' => 1800
        ]);

        if($response->getStatusCode() !== 200){
            return new JsonResponse('Bigcommerce error ', $response->getStatusCode());
        };
        $vendors = $response->getContent();
        //dump(json_decode($vendors));
        return new JsonResponse(json_decode($vendors), 200);
    }

    public function getBrands(){
        $filter ="page=1&limit=250";
        $brands = $this->clientGet('/catalog/brands', $filter);
        return $brands;
    }

    public function postBrand($body){
        $response = $this->clientPost('/catalog/brands', $body);
        //dump($response);
        return $response;
    }

    public function getItems(){
        $filter ="page=1&limit=250";
        $index = $this->clientGet('/catalog/products', $filter);
        $pages = json_decode($index->getContent())->meta->pagination->total_pages;
        dump($pages);
        $allItems = [];
        for($i=1; $i<=$pages; $i++){
            $filter = "page=".(string)$i."&limit=250";
            dump($filter);
            $items = $this->clientGet('/catalog/products', $filter);
            array_push($allItems, $items);
        }
        return $allItems;
    }

    public function setItemVisibility($id, $body){
        $response = $this->clientPut('/catalog/products', $id, $body);
        //dump($response);
        //dump("visibility set");
        return $response;
    }

    public function postProduct($body){
        $response = $this->clientPost('/catalog/products', $body);
        //dump($response);
        dump($response->getContent());
        dump("product sent");
        return $response;
    }

    public function getImages($id){
        $images = $this->clientGetImage('/catalog/products', $id, '/images');
        return $images;
    }

    public function getOrders($min){
        $orders = $this->clientGetOrder('/orders', $min);
        //dump($orders);
        return $orders;
    }

    public function getOrderItems($resource){
        $orderItems = $this->clientGetOrderItems($resource);
        return $orderItems;
    }

    public function getShipping($resource){
        $shipping = $this->clientGetShipping($resource);
        return $shipping;
    }

}