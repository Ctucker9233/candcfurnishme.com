<?php

namespace App\Http;

interface ProfitSendClientInterface
{
    public function sendProfitOrder(string $tenant, $body);
}