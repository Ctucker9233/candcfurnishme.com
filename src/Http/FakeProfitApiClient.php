<?php

namespace App\Http;

use Symfony\Component\HttpFoundation\JsonResponse;

class FakeProfitApiClient implements ProfitApiClientInterface
{

    public static $statusCode = 200;
    public static $content = '';

    public function fetchCustomerProfile(string $customer, string $tenant): JsonResponse
    {
        return new JsonResponse(self::$content, self::$statusCode, [], $json=true);
    }

    public function setContent(array $overrides): void
    {
        self::$content = json_encode(array_merge([
            "name"=>"CASH CUSTOMER",
            "address1"=>"",
            "address2"=>""
        ], $overrides));
    }
}