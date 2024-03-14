<?php

namespace App\Http\Service\Api;

use Exception;
use App\Events\NotificationPusher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;


class Api
{

  public function getOrdersWoocommerce($status, $per_page, $page, $after = false){

    $customer_key = config('app.woocommerce_customer_key');
    $customer_secret = config('app.woocommerce_customer_secret');

    if($after){
      $data = [
        'status' => $status,
        'per_page' => $per_page,
        'page' => $page,
        'after' => $after,
        'ver' => time()
      ];
    } else {
      $data = [
        'status' => $status,
        'per_page' => $per_page,
        'page' => $page,
        'ver' => time(),
      ];
    }

    try{
      $response = Http::withBasicAuth($customer_key, $customer_secret)
      ->withHeaders([
          'Cache-Control' => 'no-cache, must-revalidate, max-age=0, no-store, private',
          'Expires' => 'Wed, 11 Jan 1984 05:00:00 GMT',
      ])
      ->get(config('app.woocommerce_api_url') . "wp-json/wc/v3/orders", $data);
      return $response->json();
    } catch(Exception $e){
      return $e->getMessage();
    }
  }


  public function getOrdersWoocommerceByOrderId($order_id){

    $customer_key = config('app.woocommerce_customer_key');
    $customer_secret = config('app.woocommerce_customer_secret');

    try{
      $response = Http::withBasicAuth($customer_key, $customer_secret)->get(config('app.woocommerce_api_url')."wp-json/wc/v3/orders/".$order_id);
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

  public function updateDataOrdersWoocommerce($data, $id){

    $customer_key = config('app.woocommerce_customer_key');
    $customer_secret = config('app.woocommerce_customer_secret');

    try{
      $response = Http::withBasicAuth($customer_key, $customer_secret)->post(config('app.woocommerce_api_url')."wp-json/wc/v3/orders/".$id, $data);
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

  // Récupère les produits publiés sur la boutique
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


    // Récupère les users
    public function getDistributeurs($per_page, $page, $role){

      $customer_key = config('app.woocommerce_customer_key');
      $customer_secret = config('app.woocommerce_customer_secret');

      
      try{
        $response = Http::withBasicAuth($customer_key, $customer_secret)->get(config('app.woocommerce_api_url')."wp-json/wc/v3/customers?per_page=".$per_page."&page=".$page."&role=".$role);
        return $response->json();
  
      } catch(Exception $e){
        return $e->getMessage();
      }
    }


    public function getListRole(){

      $customer_key = config('app.woocommerce_customer_key');
      $customer_secret = config('app.woocommerce_customer_secret');

      
      try{
        $response = Http::withBasicAuth($customer_key, $customer_secret)->get(config('app.woocommerce_api_url')."wp-json/wc/v3");
        return $response->json();
  
      } catch(Exception $e){
        return $e->getMessage();
      }
    }


  public function deleteProductOrderWoocommerce($order_id, $line_item_id, $increase, $quantity, $product_id){

    $customer_key = config('app.woocommerce_customer_key');
    $customer_secret = config('app.woocommerce_customer_secret');

    if($increase == 1){
      $getProductQuantity = Http::withBasicAuth($customer_key, $customer_secret)
        ->get(config('app.woocommerce_api_url')."wp-json/wc/v3/products/".$product_id);
        
      $newQuantity = $getProductQuantity->json()['stock_quantity'] + $quantity;

      // Si c'est une variation
      if($getProductQuantity->json()['parent_id'] != 0){
        $updateProductQuantity  = Http::withBasicAuth($customer_key, $customer_secret)
          ->post(config('app.woocommerce_api_url')."wp-json/wc/v3/products/".$getProductQuantity->json()['parent_id']."/variations/".$product_id, [
              "stock_quantity" => $newQuantity
          ]);
        
      // Si c'est un produit sans variation
      } else {
        $updateProductQuantity  = Http::withBasicAuth($customer_key, $customer_secret)
          ->post(config('app.woocommerce_api_url')."wp-json/wc/v3/products/".$product_id, [
              "stock_quantity" => $newQuantity
          ]);
      }

    } 

    try {
      $orderItems = [
          [
              "id" => $line_item_id,
              "quantity" => 0,
              "meta_data" => []
          ],
          // Autres éléments de commande
      ];
  
      $response = Http::withBasicAuth($customer_key, $customer_secret)
          ->put(config('app.woocommerce_api_url')."wp-json/wc/v3/orders/".$order_id, [
              "line_items" => $orderItems
          ]);
  
        return $response->json();
    } catch (Exception $e) {
        return $e->getMessage();
    }
  }

  public function addProductOrderWoocommerce($order_id, $product , $quantity){

    $customer_key = config('app.woocommerce_customer_key');
    $customer_secret = config('app.woocommerce_customer_secret');

    try {

      $orderItems = [
          [
              "product_id" => $product,
              "quantity" => $quantity,
          ],
      ];

     
      $response = Http::withBasicAuth($customer_key, $customer_secret)
          ->post(config('app.woocommerce_api_url')."wp-json/wc/v3/orders/".$order_id, [
              "line_items" => $orderItems
      ]);

      $getProductQuantity = Http::withBasicAuth($customer_key, $customer_secret)
          ->get(config('app.woocommerce_api_url')."wp-json/wc/v3/products/".$product);


      $newQuantity = $getProductQuantity->json()['stock_quantity'] - $quantity;

      // Si c'est une variation
      if($getProductQuantity->json()['parent_id'] != 0){
        $updateProductQuantity  = Http::withBasicAuth($customer_key, $customer_secret)
          ->post(config('app.woocommerce_api_url')."wp-json/wc/v3/products/".$getProductQuantity->json()['parent_id']."/variations/".$product, [
              "stock_quantity" => $newQuantity
          ]);
        
      // Si c'est un produit sans variation
      } else {
        $updateProductQuantity  = Http::withBasicAuth($customer_key, $customer_secret)
          ->post(config('app.woocommerce_api_url')."wp-json/wc/v3/products/".$product, [
              "stock_quantity" => $newQuantity
          ]);
      }

        return $response->json();
    } catch (Exception $e) {
        return false;
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

    
    
    if (curl_errno($curl)) {
      switch ($http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE)) {
        case 200:  # OK
          break;
        default:
        echo json_encode(['success' => false, 'message'=> 'Erreur code : '. $http_code.' !']);
        exit;
          
      }
    }

    curl_close($curl);
     
    // renvoi le resultat sous forme de json
    return $result;   
  }
  
   public function getkeydolibar(){
    $apiKey = env('KEY_API_DOLIBAR');
     return $apiKey;
   }

   public function getUrldolibar(){
        $apiUrl = env('KEY_API_URL');
       return $apiUrl;

   }


   public function getkeydolibarprod(){
    $apiKey = env('KEY_API_DOLIBAR_PROD');
     return $apiKey;
   }

   public function getUrldolibarprod(){
        $apiUrl = env('KEY_API_URL_PROD');
       return $apiUrl;

   }

  public function getLabelsfromOrder($orders){
    
    // keys authentification API data woocomerce dev copie;
    $customer_key = config('app.woocommerce_customer_key');
    $customer_secret = config('app.woocommerce_customer_secret');

    try{
      $response = Cache::remember('labelsMissing', 60, function () use ($customer_key, $customer_secret, $orders) {
        $resp = Http::withBasicAuth($customer_key, $customer_secret)
          ->post(config('app.woocommerce_api_url')."wp-json/wc/v3/labels/getAllLabelsByOrderId", [
            "order_id" => $orders
        ]);

        if($resp->successful()){
          return $resp->json();
        } else {
          return false;
        }
        return $resp->json();
      });

      return $response;

    } catch (Exception $e){
      return $e->getMessage();
    }
  }

  public function CallAPI2($method, $key, $url, $data = false){
    
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



  if (curl_errno($curl)) {
    switch ($http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE)) {
      case 200:  # OK
        break;
      default:
      echo json_encode(['success' => false, 'message'=> 'Erreur code : '. $http_code.' !']);
      exit;
        
    }
  }

  // curl_close($curl);
  
  // renvoi le resultat sous forme de json
  return $result;   
}

  public function insertCronRequest($data){

    $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIzIiwianRpIjoiMjkyZmIwNDY5NmQ2MDE5NDU1MDNiZTA2NTVkZTExNWM1MDIxYmIzOTNjNDk4MWZmZjk4MjA4ZGY5ZmE4NGNjOGI4N2M1MmVhMDkzYTdjZmEiLCJpYXQiOjE2ODIzMjYyOTEuMjMwNTE3LCJuYmYiOjE2ODIzMjYyOTEuMjMwNTQxLCJleHAiOjQ4MzgwMDM0OTAuODExMTM3LCJzdWIiOiIiLCJzY29wZXMiOlsiKiJdfQ.Gk2PIxGYUHsvwRuTMxzmZ8o4iip8qdoaowIgDbBceAXqic9Kb_MmflUKkcGSKiICSR8DvcRjVCuF5waFyyEkvQ";
    $url = "https://www.cron.elyamaje.com/api/cron";
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $token)); 
    $response = curl_exec($ch); 
    curl_close($ch); 
  

    return $response;

  }

  function getAlertes(){

    $dossiers = [
      "chemin_reassorts" => storage_path('app/public/reassorts/notraite'),
      "chemin_alertes" => storage_path('app/public/alertes/notraite')
    ];

    $nbr_alertes_reassorts = 0;
    $nbr_alerte_stock = 0;

    foreach ($dossiers as $key => $chemin) {

      if ($key == "chemin_reassorts") {

        $fichiers1 = File::files($chemin);
        $nbr_alertes_reassorts = $nbr_alertes_reassorts + count($fichiers1);

      }elseif ($key == "chemin_alertes") {

        $fichiers2 = File::files($chemin);
        $nbr_alerte_stock = $nbr_alerte_stock + count($fichiers2);

      }

    

    }

    // Les reassort en attente 
    $alerte_reassortEnAttente = DB::table('hist_reassort')
      ->where('id_reassort', 0)
      ->distinct()
      ->count('identifiant_reassort');


    return [
      "alerte_stockReassort" => $nbr_alerte_stock + $nbr_alertes_reassorts,
      "alerte_reassortEnAttente" => $alerte_reassortEnAttente
    ];
    

  }

  function updateSessionStockAlerte($cle, $value){

  

    try {

      // Pusher notification partial order  
      $notification_push = [

        'cle' => $cle,
        'value' => $value,
        'type' => "alerteStock"
      ];      

      event(New NotificationPusher($notification_push));

      return true;

    } catch (\Throwable $th) {
      return false;;
    }



  }

 




}







