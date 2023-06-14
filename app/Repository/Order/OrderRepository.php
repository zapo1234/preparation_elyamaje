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
            $amount = [];

            if(isset($orderData['coupon_lines'])){
               foreach($orderData['coupon_lines'] as $coupon){
                  $coupons[] = $coupon['code'];
                  $discount[] = $coupon['discount'];
                  $amount[] = isset($coupon['meta_data'][0]['value']['amount']) ? $coupon['meta_data'][0]['value']['amount'] : 0;
               }
            }

            // Utilisation de la fonction pour récupérer la valeur avec la clé "_lpc_meta_pickUpProductCode"
            $productCode = $this->getValueByKey($orderData['meta_data'], "_lpc_meta_pickUpProductCode");

            if(isset($orderData['cart_hash'])){
               $ordersToInsert[] = [
                  'order_woocommerce_id' => $orderData['id'],
                  'customer_id' => $orderData['customer_id'],
                  'coupons' => count($coupons) > 0 ? implode(',', $coupons) : "",
                  'discount' => count($discount) > 0 ? implode(',', $discount) : 0,
                  'discount_amount' => count($amount) > 0 ? implode(',', $amount) : 0,
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
                  'status' => $orderData['status'],
                  'shipping_method' => isset($orderData['shipping_lines'][0]['method_id']) ? $orderData['shipping_lines'][0]['method_id'] : null,
                  'product_code' => $productCode,
               ];
               

               foreach($orderData['line_items'] as $value){

                  if($value['meta_data']){
                     // $barcode = $value['meta_data'][array_key_last($value['meta_data'])]['key'] == "barcode" ? $value['meta_data'][array_key_last($value['meta_data'])]['value'] : null;
                     $weight = $value['meta_data'][array_key_last($value['meta_data'])]['key'] == "weight" ? $value['meta_data'][array_key_last($value['meta_data'])]['value'] : null;
                  } else {
                     // $barcode = null;
                     $weight = 0;
                  }  

                  $productsToInsert[] = [
                     'order_id' => $orderData['id'],
                     'product_woocommerce_id' => $value['variation_id'] != 0 ? $value['variation_id'] : $value['product_id'],
                     'category' =>  isset($value['category'][0]['name']) ? $value['category'][0]['name'] : '',
                     'category_id' => isset($value['category'][0]['term_id']) ? $value['category'][0]['term_id'] : '',
                     'quantity' => $value['quantity'],
                     'cost' => $value['price'],
                     'subtotal_tax' =>  $value['subtotal_tax'],
                     'total_tax' =>  $value['total_tax'],
                     'total_price' => $value['subtotal'],
                     'weight' => $weight,
                     'line_item_id' => $value['id']
                  ];
               }
            }
          
         }
    
         // Insérer les données dans la base de données par lot
         try{
            $this->model->insert($ordersToInsert);
            DB::table('products_order')->insert($productsToInsert);
         } catch(Exception $e){ 
            continue;
         }

      }

      echo json_encode(['success' => true]);

    }


   public function getOrdersByUsers(){
      return $this->model->select('orders.*', 'users.name')->where('status', 'processing')->join('users', 'users.id', '=', 'orders.user_id')->get();
   }

   public function getAllOrdersByUsersNotFinished(){
      return $this->model->select('orders.*', 'users.name')->where('status', '!=', 'finished')->join('users', 'users.id', '=', 'orders.user_id')->get();
   }


   public function getUsersWithOrder(){
      return $this->model->select('users.*')->where('status', 'processing')->join('users', 'users.id', '=', 'orders.user_id')->groupBy('users.id')->get();
   }

   public function getOrdersByIdUser($id, $distributeur = false){
      $distributeurs_id = ['4996', '4997', '1707', '3550', '3594'];

      $list = [];
      $list2 = [];

      // Pour filtrer les gels par leurs attributs les 20 puis les 50 après
      // $queryOrder = "CASE WHEN products_order.name LIKE '%20 ml' THEN 1 ";
      // $queryOrder .= "WHEN products_order.name LIKE '%50 ml' THEN 2 ";
      // $queryOrder .= "ELSE 3 END";

      $orders = 
      $this->model->join('products_order', 'products_order.order_id', '=', 'orders.order_woocommerce_id')
         ->join('products', 'products.product_woocommerce_id', '=', 'products_order.product_woocommerce_id')
         ->join('categories', 'products_order.category_id', '=', 'categories.category_id_woocommerce')
         ->where('user_id', $id)
         ->whereIn('orders.status', ['processing', 'waiting_to_validate', 'waiting_validate'])
         ->select('orders.*', 'products.product_woocommerce_id', 'products.category', 'products.category_id', 'products.variation',
         'products.name', 'products.barcode', 'categories.order_display', 'products_order.pick', 'products_order.quantity',
         'products_order.subtotal_tax', 'products_order.total_tax','products_order.total_price', 'products_order.cost', 'products_order.weight')
         ->orderBy('orders.date', 'ASC')
         ->orderBy('categories.order_display', 'ASC')
         // ->orderByRaw($queryOrder)
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
                  'total' => $order['total_order'],
                  'total_tax' => $order['total_tax_order'],
                  'status' => $order['status'],
                  'coupons' => $order['coupons'],
                  'discount' => $order['discount'],
                  'discount_amount' => $order['discount_amount'],
               ];
               $list[$order['order_woocommerce_id']]['items'][] = $order;
            }
         } else {
            if(!in_array($order['customer_id'], $distributeurs_id)){
               $list[$order['order_woocommerce_id']]['details'] = [
                  'id' => $order['order_woocommerce_id'],
                  'first_name' => $order['billing_customer_first_name'],
                  'last_name' => $order['billing_customer_last_name'],
                  'date' => $order['date'],
                  'total' => $order['total_order'],
                  'total_tax' => $order['total_tax_order'],
                  'status' => $order['status'],
                  'coupons' => $order['coupons'],
                  'discount' => $order['discount'],
                  'discount_amount' => $order['discount_amount'],
               ];
               $list[$order['order_woocommerce_id']]['items'][] = $order;
            }
         }
      }

      $list = array_values($list);
      return $list;
   }

   public function updateOrdersById($ids, $status = "finished"){
      try{
         $this->model::whereIn('order_woocommerce_id', $ids)->update(['status' => $status]);
         return true;
      } catch(Exception $e){
         return $e->getMessage();
      }
   }

   public function checkIfDone($order_id, $barcode_array, $partial = false) {

      $list_prouct_orders = DB::table('products')
      ->select('barcode')
      ->join('products_order', 'products_order.product_woocommerce_id', '=', 'products.product_woocommerce_id')
      ->where('products_order.order_id', $order_id)
      ->get();


      $update_products = DB::table('products_order')
      ->join('products', 'products.product_woocommerce_id', '=', 'products_order.product_woocommerce_id')
      ->whereIn('barcode', $barcode_array)
      ->where('order_id', $order_id)
      ->update(['pick' => 1]);


      if(!$partial){
         $missing_product = false;

         foreach($list_prouct_orders as $product_order){
            if(!in_array($product_order->barcode, $barcode_array)){
               $missing_product = true;
            }
         }
   
         if(!$missing_product){
            // Modifie le status de la commande en "Commande préparée",
            $update_status_local = $this->updateOrdersById([$order_id], "prepared-order");
   
            // Insert la commande dans histories
            DB::table('histories')->insert([
               'order_id' => $order_id,
               'user_id' => Auth()->user()->id,
               'status' => 'prepared',
            ]);
            
            // Modifie le status de la commande sur Woocommerce en "Commande préparée"
            try{
               $this->api->updateOrdersWoocommerce("prepared-order", $order_id);
               return true;
            } catch(Exception $e){
               return $e->getMessage();
            }
         } else {
            return false;
         }
      } else {
          // Modifie le status de la commande en "en attente de validation"
         try{
            $this->updateOrdersById([$order_id], "waiting_to_validate");
            return true;
         } catch(Exception $e){
            return $e->getMessage();
         }
      }
   }

   public function orderReset($order_id) {
      
      try{
         $update_products = DB::table('products_order')->whereIn('order_id', [$order_id])->update(['pick' => 0]);
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
            DB::table('products_order')->where('order_id', $order_id)->delete();
         } else {
            $order = $this->model::where('order_woocommerce_id', $order_id)->get()->toArray();
            if(count($order) == 0){
               $insert_order_by_user = $this->api->getOrderById($order_id);

               $coupons = [];
               $discount = [];
               $amount = [];

               if(isset($insert_order_by_user['coupon_lines'])){
                  foreach($insert_order_by_user['coupon_lines'] as $coupon){
                     $coupons[] = $coupon['code'];
                     $discount[] = $coupon['discount'];
                     $amount[] = isset($coupon['meta_data'][0]['value']['amount']) ? $coupon['meta_data'][0]['value']['amount'] : 0;
                  }
               }

               // Utilisation de la fonction pour récupérer la valeur avec la clé "_lpc_meta_pickUpProductCode"
               $productCode = $this->getValueByKey($insert_order_by_user['meta_data'], "_lpc_meta_pickUpProductCode");


               // Insert commande
               $ordersToInsert = [
                  'order_woocommerce_id' => $insert_order_by_user['id'],
                  'customer_id' => $insert_order_by_user['customer_id'],
                  'coupons' => count($coupons) > 0 ? implode(',', $coupons) : "",
                  'discount' => count($discount) > 0 ? implode(',', $discount) : 0,
                  'discount_amount' => count($amount) > 0 ? implode(',', $amount) : 0,
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
                  'status' => $insert_order_by_user['status'],
                  'shipping_method' => isset($insert_order_by_user['shipping_lines'][0]['method_id']) ? $insert_order_by_user['shipping_lines'][0]['method_id'] : null,
                  'product_code' => $productCode,
               ];

               // Insert produits
               foreach($insert_order_by_user['line_items'] as $value){

                  if($value['meta_data']){
                     // $barcode = $value['meta_data'][array_key_last($value['meta_data'])]['key'] == "barcode" ? $value['meta_data'][array_key_last($value['meta_data'])]['value'] : null;
                     $weight = $value['meta_data'][array_key_last($value['meta_data'])]['key'] == "weight" ? $value['meta_data'][array_key_last($value['meta_data'])]['value'] : null;
                  } else {
                     // $barcode = null;
                     $weight = 0;
                  }  

                  $productsToInsert[] = [
                     'order_id' => $insert_order_by_user['id'],
                     'product_woocommerce_id' => $value['variation_id'] != 0 ? $value['variation_id'] : $value['product_id'],
                     'category' =>  isset($value['category'][0]['name']) ? $value['category'][0]['name'] : '',
                     'category_id' => isset($value['category'][0]['term_id']) ? $value['category'][0]['term_id'] : '',
                     'quantity' => $value['quantity'],
                     'cost' => $value['price'],
                     'subtotal_tax' =>  $value['subtotal_tax'],
                     'total_tax' =>  $value['total_tax'],
                     'total_price' => $value['subtotal'],
                     'weight' => $weight,
                     'line_item_id' => $value['id']
                  ];
               }

               $this->model->insert($ordersToInsert);
               DB::table('products_order')->insert($productsToInsert);
            } else {
               $update_one_order_attribution =  $this->model::where('order_woocommerce_id', $order_id)->update(['user_id' => $user_id]);
            }
         }
         return true;
      } catch(Exception $e){
         return $e->getMessage();
      }
   }

   public function getOrderById($order_id){
      return $this->model::select('orders.*', 'products.*', 'products_order.pick', 'products_order.quantity',
      'products_order.subtotal_tax', 'products_order.total_tax','products_order.total_price', 'products_order.cost', 'products_order.weight')
      ->where('order_woocommerce_id', $order_id)
      ->join('products_order', 'products_order.order_id', '=', 'orders.order_woocommerce_id')
      ->join('products', 'products.product_woocommerce_id', '=', 'products_order.product_woocommerce_id')
      ->get()
      ->toArray();
   }
   
   public function getHistoryByUser($user_id){
  
      $list = [];
      // Pour filtrer les gels par leurs attributs les 20 puis les 50 après
      // $queryOrder = "CASE WHEN products.name LIKE '%20 ml' THEN 1 ";
      // $queryOrder .= "WHEN products.name LIKE '%50 ml' THEN 2 ";
      // $queryOrder .= "ELSE 3 END";

      $orders = 
      $this->model->join('products_order', 'products_order.order_id', '=', 'orders.order_woocommerce_id')
         ->join('categories', 'products_order.category_id', '=', 'categories.category_id_woocommerce')
         ->join('products', 'products.product_woocommerce_id', '=', 'products_order.product_woocommerce_id')
         ->where('user_id', $user_id)
         ->whereIn('orders.status', ['finished', 'prepared-order'])
         ->select('orders.*', 'products.*', 'categories.order_display', 'products_order.pick', 'products_order.quantity',
         'products_order.subtotal_tax', 'products_order.total_tax','products_order.total_price', 'products_order.cost', 'products_order.weight')
         ->orderBy('orders.updated_at', 'DESC')
         ->orderBy('categories.order_display', 'ASC')
         // ->orderByRaw($queryOrder)
         ->get();

      $orders = json_decode(json_encode($orders), true);

      foreach($orders as $key => $order){
         $list[$order['order_woocommerce_id']]['details'] = [
            'id' => $order['order_woocommerce_id'],
            'first_name' => $order['billing_customer_first_name'],
            'last_name' => $order['billing_customer_last_name'],
            'date' => $order['date'],
            'total' => $order['total_order'],
            'total_tax' => $order['total_tax_order'],
            'status' => $order['status'],
            'coupons' => $order['coupons'],
            'discount' => $order['discount'],
            'discount_amount' => $order['discount_amount'],
         ];
         $list[$order['order_woocommerce_id']]['items'][] = $order;
      }

      $list = array_values($list);
      return $list;
   }


   // Fonction pour récupérer la valeur avec une clé spécifique
   public function getValueByKey($array, $key) {
       foreach ($array as $item) {
           if ($item['key'] === $key) {
               return $item['value'];
           }
       }
       return null; // Si la clé n'est pas trouvée
   }

   public function updateTotalOrders($data){
      return $this->model->update([
         'total_tax_order' => $data['total_tax'],
         'total_order' => $data['total']
      ]);
   }
}























