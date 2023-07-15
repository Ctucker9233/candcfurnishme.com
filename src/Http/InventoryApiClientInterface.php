<?php

namespace App\Http;

interface InventoryApiClientInterface
{
    public function fetchItems(string $products, string $tenant, $id);
}