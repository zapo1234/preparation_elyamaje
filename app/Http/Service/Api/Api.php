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

    // Cache commenté car peux poser problème
    // $result = Cache::remember('orders', 15, function () use ($status, $page, $per_page, $customer_key, $customer_secret) {
      $response = Http::withBasicAuth($customer_key, $customer_secret)->get("https://www.staging.elyamaje.com/wp-json/wc/v3/orders?status=".$status."&per_page=".$per_page."&page=".$page);
      return $response->json();
    // });
    
    // return $result;
  }


  public function updateOrdersWoocommerce($status, $id){

    $customer_key="ck_06dc2c28faab06e6532ecee8a548d3d198410969";
    $customer_secret ="cs_a11995d7bd9cf2e95c70653f190f9feedb52e694";

    // Cache commenté car peux poser problème
    // $result = Cache::remember('orders', 15, function () use ($status, $page, $per_page, $customer_key, $customer_secret) {
      try{
        $response = Http::withBasicAuth($customer_key, $customer_secret)->post("https://www.staging.elyamaje.com/wp-json/wc/v3/orders/".$id, [
          'status' => $status,
        ]);

        return $response->json() ? true : false;

      } catch(Exception $e){
        return $e->getMessage();
      }
  }

  public function insertOrderByUser($order_id, $user_id){

    $customer_key="ck_06dc2c28faab06e6532ecee8a548d3d198410969";
    $customer_secret ="cs_a11995d7bd9cf2e95c70653f190f9feedb52e694";

    try{
      $response = Http::withBasicAuth($customer_key, $customer_secret)->get("https://www.staging.elyamaje.com/wp-json/wc/v3/orders/".$order_id);
      return $response->json();

    } catch(Exception $e){
      return $e->getMessage();
    }
  }



    // api get
    public function getDataApiWoocommerce(string $urls): array
    {
        
       // keys authentification API data woocomerce dev copie;
        $customer_key="ck_06dc2c28faab06e6532ecee8a548d3d198410969";
        $customer_secret ="cs_a11995d7bd9cf2e95c70653f190f9feedb52e694";
        
       $headers = array(
           
           'Authorization'=> 'Basic' .base64_encode($customer_key.':'.$customer_secret)
            );
            
         //
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


  public function CallAPI($method, $key, $url, $data = false)
  {
      
        $curl = curl_init();
        $httpheader = ['DOLAPIKEY: '.$key];

      switch ($method)
     {
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







