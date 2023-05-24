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
            $coupons = [];
            $discount = [];
            if(isset($orderData['coupon_lines'])){
               foreach($orderData['coupon_lines'] as $coupon){
                  $coupons[] = $coupon['code'];
                  $discount[] = $coupon['discount'];
               }
            }


            if(isset($orderData['cart_hash'])){
               $ordersToInsert[] = [
                  'order_woocommerce_id' => $orderData['id'],
                  'customer_id' => $orderData['customer_id'],
                  'coupons' => count($coupons) > 0 ? implode(',', $coupons) : null,
                  'discount' => count($discount) > 0 ? implode(',', $discount) : 0,
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
                  'total_tax_order' => $orderData['total_tax'],
                  'total_order' => $orderData['total'],
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
                     'product_woocommerce_id' => $value['product_id'],
                     'category' =>  isset($value['category'][0]['name']) ? $value['category'][0]['name'] : '',
                     'category_id' => isset($value['category'][0]['term_id']) ? $value['category'][0]['term_id'] : '',
                     'variation_id' => $value['variation_id'] ?? null,
                     'name' => $value['name'],
                     'quantity' => $value['quantity'],
                     'barcode' => $barcode, // Get barcode in meta_data (last key),
                     'cost' => $value['total'],
                     'subtotal_tax' =>  $value['subtotal_tax'],
                     'total_tax' =>  $value['total_tax'],
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

      echo json_encode(['success' => true]);

    }


   public function getOrdersByUsers(){
      return $this->model->select('orders.*', 'users.name')->where('status', 'processing')->join('users', 'users.id', '=', 'orders.user_id')->get();
   }


   public function getUsersWithOrder(){
      return $this->model->select('users.*')->where('status', 'processing')->join('users', 'users.id', '=', 'orders.user_id')->groupBy('users.id')->get();
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
      } else {
         return json_encode(['success' => false]);
      }
   }

   public function orderReset($order_id) {
      
      try{
         $update_products = DB::table('products')->whereIn('order_id', [$order_id])->update(['pick' => 0]);
         return true;
      } catch(Exception $e){
         return false;
      }

   }

   public function updateOrderAttribution($from_user, $to_user){
      try{
         $update_order_attribution =  $this->model::where('user_id', $from_user)->update(['user_id' => $to_user]);
         return true;
      } catch(Exception $e){
         return false;
      }
   }

   public function updateOneOrderAttribution($order_id, $user_id){
      try{

         if($user_id == "Non attribuée"){
            $order = $this->model::where('order_woocommerce_id', $order_id)->delete();
            DB::table('products')->where('order_id', $order_id)->delete();
         } else {
            $order = $this->model::where('order_woocommerce_id', $order_id)->get()->toArray();
            if(count($order) == 0){
               $insert_order_by_user = $this->api->insertOrderByUser($order_id, $user_id);

               $coupons = [];
               $discount = [];
               if(isset($insert_order_by_user['coupon_lines'])){
                  foreach($insert_order_by_user['coupon_lines'] as $coupon){
                     $coupons[] = $coupon['code'];
                     $discount[] = $coupon['discount'];
                  }
               }

               // Insert commande
               $ordersToInsert = [
                  'order_woocommerce_id' => $insert_order_by_user['id'],
                  'customer_id' => $insert_order_by_user['customer_id'],
                  'coupons' => count($coupons) > 0 ? implode(',', $coupons) : null,
                  'discount' => count($discount) > 0 ? implode(',', $discount) : 0,
                  'billing_customer_first_name' => $insert_order_by_user['billing']['first_name'] ?? null,
                  'billing_customer_last_name' => $insert_order_by_user['billing']['last_name'] ?? null,
                  'billing_customer_company' => $insert_order_by_user['billing']['company'] ?? null,
                  'billing_customer_address_1' => $insert_order_by_user['billing']['address_1'] ?? null,
                  'billing_customer_address_2' => $insert_order_by_user['billing']['address_2'] ?? null,
                  'billing_customer_city' => $insert_order_by_user['billing']['city'] ?? null,
                  'billing_customer_state' => $insert_order_by_user['billing']['state'] ?? null,
                  'billing_customer_postcode' => $insert_order_by_user['billing']['postcode'] ?? null,
                  'billing_customer_country' => $insert_order_by_user['billing']['country'] ?? null,
                  'billing_customer_email' => $insert_order_by_user['billing']['email'] ?? null,
                  'billing_customer_phone' => $insert_order_by_user['billing']['phone'] ?? null,
                  'shipping_customer_first_name' => $insert_order_by_user['shipping']['first_name'] ?? null,
                  'shipping_customer_last_name' => $insert_order_by_user['shipping']['last_name'] ?? null,
                  'shipping_customer_company' => $insert_order_by_user['shipping']['company'] ?? null,
                  'shipping_customer_address_1' => $insert_order_by_user['shipping']['address_1'] ?? null,
                  'shipping_customer_address_2' => $insert_order_by_user['shipping']['address_2'] ?? null,
                  'shipping_customer_city' => $insert_order_by_user['shipping']['city'] ?? null,
                  'shipping_customer_state' => $insert_order_by_user['shipping']['state'] ?? null,
                  'shipping_customer_postcode' => $insert_order_by_user['shipping']['postcode'] ?? null,
                  'shipping_customer_country' => $insert_order_by_user['shipping']['country'] ?? null,
                  'shipping_customer_phone' => $insert_order_by_user['shipping']['phone'] ?? null,
                  'date' => $insert_order_by_user['date_created'],
                  'total_tax_order' => $insert_order_by_user['total_tax'],
                  'total_order' => $insert_order_by_user['total'],
                  'user_id' => $user_id,
                  'status' => $insert_order_by_user['status']
               ];

               // Insert produits
               foreach($insert_order_by_user['line_items'] as $value){

                  if($value['meta_data']){
                     $barcode = $value['meta_data'][array_key_last($value['meta_data'])]['key'] == "barcode" ? $value['meta_data'][array_key_last($value['meta_data'])]['value'] : null;
                  } else {
                     $barcode = null;
                  }

                  $productsToInsert[] = [
                     'order_id' => $insert_order_by_user['id'],
                     'product_woocommerce_id' => $value['product_id'],
                     'category' =>  isset($value['category'][0]['name']) ? $value['category'][0]['name'] : '',
                     'category_id' => isset($value['category'][0]['term_id']) ? $value['category'][0]['term_id'] : '',
                     'variation_id' => $value['variation_id'] ?? null,
                     'name' => $value['name'],
                     'quantity' => $value['quantity'],
                     'barcode' => $barcode, // Get barcode in meta_data (last key),
                     'cost' => $value['total'],
                     'subtotal_tax' =>  $value['subtotal_tax'],
                     'total_tax' =>  $value['total_tax'],
                     'total_price' => floatval($value['quantity']) * floatval($value['total'])
                  ];
               }
   
               $this->model->insert($ordersToInsert);
               DB::table('products')->insert($productsToInsert);
            } else {
               $update_one_order_attribution =  $this->model::where('order_woocommerce_id', $order_id)->update(['user_id' => $user_id]);
            }
         }
         return true;
      } catch(Exception $e){
         return false;
      }
   }


   public function getOrderById($order_id){
      return $this->model::select('orders.*', 'products.*')->where('order_woocommerce_id', $order_id)->join('products', 'products.order_id', '=', 'orders.order_woocommerce_id')->get()->toArray();
   }
}























