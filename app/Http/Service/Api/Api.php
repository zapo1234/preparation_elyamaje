<?php

namespace App\Http\Service\Api;

use Exception;
use Illuminate\Support\Facades\Http;

class Api
{

  public function getOrdersWoocommerce($status, $per_page, $page){

    $customer_key = config('app.woocommerce_customer_key');
    $customer_secret = config('app.woocommerce_customer_secret');

    try{
      $response = Http::withBasicAuth($customer_key, $customer_secret)->get(config('app.woocommerce_api_url')."wp-json/wc/v3/orders?status=".$status."&per_page=".$per_page."&page=".$page);
      return $response->json();
    } catch(Exception $e){
      return $e->getMessage();
    }
  }

  public function updateOrdersWoocommerce($status, $id){

    $customer_key = config('app.woocommerce_customer_key');
    $customer_secret = config('app.woocommerce_customer_secret');

    try{
      $response = Http::withBasicAuth($customer_key, $customer_secret)->post(config('app.woocommerce_api_url')."wp-json/wc/v3/orders/".$id, [
        'status' => $status,
      ]);
      return $response->json() ? true : false;
    } catch(Exception $e){
      return $e->getMessage();
    }
  }

  public function getOrderById($order_id){

    $customer_key = config('app.woocommerce_customer_key');
    $customer_secret = config('app.woocommerce_customer_secret');

    try{
      $response = Http::withBasicAuth($customer_key, $customer_secret)->get(config('app.woocommerce_api_url')."wp-json/wc/v3/orders/".$order_id);
      return $response->json();
    } catch(Exception $e){
      return $e->getMessage();
    }
  }

  public function getAllCategories($per_page, $page){

    $customer_key = config('app.woocommerce_customer_key');
    $customer_secret = config('app.woocommerce_customer_secret');

    try{
      $response = Http::withBasicAuth($customer_key, $customer_secret)->get(config('app.woocommerce_api_url')."wp-json/wc/v3/products/categories?per_page=".$per_page."&page=".$page);
      return $response->json();

    } catch(Exception $e){
      return $e->getMessage();
    }
  }

  public function getAllProducts($per_page, $page){

    $customer_key = config('app.woocommerce_customer_key');
    $customer_secret = config('app.woocommerce_customer_secret');

    try{
      $response = Http::withBasicAuth($customer_key, $customer_secret)->get(config('app.woocommerce_api_url')."wp-json/wc/v3/products?per_page=".$per_page."&page=".$page);
      return $response->json();

    } catch(Exception $e){
      return $e->getMessage();
    }
  }

  public function getDataApiWoocommerce(string $urls): array{
      
      // keys authentification API data woocomerce dev copie;
      $customer_key = config('app.woocommerce_customer_key');
      $customer_secret = config('app.woocommerce_customer_secret');
      
      $headers = array(
        'Authorization'=> 'Basic' .base64_encode($customer_key.':'.$customer_secret)
      );
          
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $urls);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($curl, CURLOPT_USERPWD, "$customer_key:$customer_secret");
      $resp = curl_exec($curl);
      $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE); 
      curl_close($curl);
    
      // afficher les données dans array
      $data = json_decode($resp,true);
      return $data; 
  }

  public function CallAPI($method, $key, $url, $data = false){
      
        $curl = curl_init();
        $httpheader = ['DOLAPIKEY: '.$key];

      switch ($method){
        case "POST":
          curl_setopt($curl, CURLOPT_POST, 1);
          $httpheader[] = "Content-Type:application/json";

          if ($data)
              curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
          break;
       case "PUT":

       curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
          $httpheader[] = "Content-Type:application/json";

          if ($data)
              curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

          break;
      default:
          if ($data)
              $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $httpheader);

    $result = curl_exec($curl);

    curl_close($curl);
     
    // renvoi le resultat sous forme de json
    return $result;   
  }  
}







