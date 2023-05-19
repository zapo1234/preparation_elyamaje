<?php

// recuperer des utilitaires

namespace App\Http\Service\Api;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;


class Api
{

  public function getOrdersWoocommerce($status, $per_page, $page){

    $customer_key="ck_06dc2c28faab06e6532ecee8a548d3d198410969";
    $customer_secret ="cs_a11995d7bd9cf2e95c70653f190f9feedb52e694";

    $result = Cache::remember('orders', 15, function () use ($status, $page, $per_page, $customer_key, $customer_secret) {
      $response = Http::withBasicAuth($customer_key, $customer_secret)->get("https://www.staging.elyamaje.com/wp-json/wc/v3/orders?status=".$status."&per_page=".$per_page."&page=".$page);
      return $response->json();
    });
    
    return $result;
  }

}







