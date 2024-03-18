<?php

namespace App\Services;
use App\Http\Service\Api\Api;

class SupplierOrderService
{
    public function getAllSupplierOrders()
    {
        try {

            $apiKey = "f2HAnva64Zf9MzY081Xw8y18rsVVMXaQ"; //env('KEY_API_DOLIBAR'); 
            $apiUrl = "https://www.transfertx.elyamaje.com/api/index.php/"; //env('KEY_API_DOLIBAR'); 
            
            $filters = array(
                'apikey' => $apiKey,
                'limit' => 100,
            );

            $method = "GET";
            $endpoint = "supplierorders";
            $api = new Api();
            $result = $api->CallAPI($method, $apiKey, $apiUrl.$endpoint,$filters);
            $result = json_decode($result, true);
            return $result;

        } catch (\Throwable $th) {
            return false;
        }

    }

}