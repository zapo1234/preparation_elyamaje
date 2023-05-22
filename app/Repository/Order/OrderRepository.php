<?php

namespace App\Repository\Order;

use Hash;
use Exception;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use App\Http\Service\Api\Api;
use Illuminate\Support\Facades\DB;

class OrderRepository implements OrderInterface

{

   private $model;
   private $api;

   public function __construct(Order $model, Api $api){
      $this->model = $model;
      $this->api = $api;
   }


   public function insertOrdersByUsers($array_user){

     // Parcourir les données des utilisateurs
      foreach ($array_user as $userId => $userOrders) {
         $ordersToInsert = [];
         $productsToInsert = [];
    
         // Construire un tableau des données d'insertion pour l'utilisateur actuel
         foreach ($userOrders as $orderData) {
            
            // Récupérer que les commandes venant de woocommerce, les autres sont déjà en base pas besoin de réinsérer
            if(isset($orderData['cart_hash'])){
               $ordersToInsert[] = [
                  'order_woocommerce_id' => $orderData['id'],
                  'customer_id' => $orderData['customer_id'],

                  'billing_customer_first_name' => $orderData['billing']['first_name'] ?? null,
                  'billing_customer_last_name' => $orderData['billing']['last_name'] ?? null,
                  'billing_customer_company' => $orderData['billing']['company'] ?? null,
                  'billing_customer_address_1' => $orderData['billing']['address_1'] ?? null,
                  'billing_customer_address_2' => $orderData['billing']['address_2'] ?? null,
                  'billing_customer_city' => $orderData['billing']['city'] ?? null,
                  'billing_customer_state' => $orderData['billing']['state'] ?? null,
                  'billing_customer_postcode' => $orderData['billing']['postcode'] ?? null,
                  'billing_customer_country' => $orderData['billing']['country'] ?? null,
                  'billing_customer_email' => $orderData['billing']['email'] ?? null,
                  'billing_customer_phone' => $orderData['billing']['phone'] ?? null,

                  'shipping_customer_first_name' => $orderData['shipping']['first_name'] ?? null,
                  'shipping_customer_last_name' => $orderData['shipping']['last_name'] ?? null,
                  'shipping_customer_company' => $orderData['shipping']['company'] ?? null,
                  'shipping_customer_address_1' => $orderData['shipping']['address_1'] ?? null,
                  'shipping_customer_address_2' => $orderData['shipping']['address_2'] ?? null,
                  'shipping_customer_city' => $orderData['shipping']['city'] ?? null,
                  'shipping_customer_state' => $orderData['shipping']['state'] ?? null,
                  'shipping_customer_postcode' => $orderData['shipping']['postcode'] ?? null,
                  'shipping_customer_country' => $orderData['shipping']['country'] ?? null,
                  'shipping_customer_phone' => $orderData['shipping']['phone'] ?? null,

                  'date' => $orderData['date_created'],
                  'total' => $orderData['total'],
                  'user_id' => $userId,
                  'status' => $orderData['status']
               ];
               

               foreach($orderData['line_items'] as $value){

                  if($value['meta_data']){
                     $barcode = $value['meta_data'][array_key_last($value['meta_data'])]['key'] == "barcode" ? $value['meta_data'][array_key_last($value['meta_data'])]['value'] : null;
                  } else {
                     $barcode = null;
                  }

                  $productsToInsert[] = [
                     'order_id' => $orderData['id'],
                     'product_woocommerce_id' => $value['variation_id'] ?? $value['product_id'],
                     'name' => $value['name'],
                     'quantity' => $value['quantity'],
                     'barcode' => $barcode, // Get barcode in meta_data (last key),
                     'cost' => $value['total'],
                     'total_price' => floatval($value['quantity']) * floatval($value['total'])
                  ];
               }
            }
          
         }
    
         // Insérer les données dans la base de données par lot
         try{
            $this->model->insert($ordersToInsert);
            DB::table('products')->insert($productsToInsert);
         } catch(Exception $e){ 
            continue;
         }

      }
      dd("Commandes bien réparties !");
    }


   public function getOrdersByUsers(){
      return $this->model->select('*')->where('status', 'processing')->get();

   }

   public function getOrdersByIdUser($id, $distributeur = false){

      $distributeurs_id = ['4996', '4997', '1707', '3550', '3594'];

      $list = [];
      $list2 = [];

      $orders = 
      $this->model->join('products', 'products.order_id', '=', 'orders.order_woocommerce_id')
         ->where('user_id', $id)
         ->where('status', 'processing')
         ->select('*')
         ->orderBy('date', 'ASC')
         ->get();

      $orders = json_decode(json_encode($orders), true);

      foreach($orders as $key => $order){
         if($distributeur){
            if(in_array($order['customer_id'], $distributeurs_id)){
               $list[$order['order_woocommerce_id']]['details'] = [
                  'id' => $order['order_woocommerce_id'],
                  'first_name' => $order['billing_customer_first_name'],
                  'last_name' => $order['billing_customer_last_name'],
                  'date' => $order['date'],
                  'total' => $order['total'],
                  'status' => $order['status'],
               ];
               $list[$order['order_woocommerce_id']]['items'][] = $order;
            }
         } else {
            $list[$order['order_woocommerce_id']]['details'] = [
               'id' => $order['order_woocommerce_id'],
               'first_name' => $order['billing_customer_first_name'],
               'last_name' => $order['billing_customer_last_name'],
               'date' => $order['date'],
               'total' => $order['total'],
               'status' => $order['status'],
            ];
            $list[$order['order_woocommerce_id']]['items'][] = $order;
         }
      }

      $list = array_values($list);

      return $list;
   }

   public function updateOrdersById($ids, $status = "done"){
      $this->model::whereIn('order_woocommerce_id', $ids)->update(['status' => $status]);
   }

   public function checkIfDone($order_id, $barcode_array) {

      $list_prouct_orders = DB::table('products')->select('barcode')->where('order_id', $order_id)->get();
      $update_products = DB::table('products')->whereIn('barcode', $barcode_array)->update(['pick' => 1]);

      $missing_product = false;

      foreach($list_prouct_orders as $product_order){
         if(!in_array($product_order->barcode, $barcode_array)){
            $missing_product = true;
         }
      }

      if(!$missing_product){
         // Modifie le status de la commande sur Woocommerce en "Commande préparée"

         $update_status_local = $this->updateOrdersById([$order_id], "prepared-order");
         $update = $this->api->updateOrdersWoocommerce("prepared-order", $order_id);
         return json_encode(['success' => is_bool($update) ?? false, "message" => is_bool($update) ? "" : $update]);
       
         // update status orders in database + woocommerce et générer code

      } else {
         return json_encode(['success' => false]);
      }
   }

   public function orderReset($order_id) {
      $update_products = DB::table('products')->whereIn('order_id', [$order_id])->update(['pick' => 0]);
      return $update_products;

   }

}























