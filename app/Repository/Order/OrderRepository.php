<?php

namespace App\Repository\Order;

use Hash;
use Exception;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use App\Http\Service\Api\Api;
use App\Models\ProductsOrder;
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
            $pickUpLocationId = $this->getValueByKey($orderData['meta_data'], "_lpc_meta_pickUpLocationId");


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

                  'payment_method' => $orderData['payment_method'] ? $orderData['payment_method'] : (count($orderData['pw_gift_cards_redeemed']) > 0 ? 'gift_card' : null ),
                  'payment_method_title' => $orderData['payment_method_title'] ? $orderData['payment_method_title'] : (count($orderData['pw_gift_cards_redeemed']) > 0 ? 'Gift Card' : null ),
                  'gift_card_amount' => isset($orderData['pw_gift_cards_redeemed'][0]['amount']) ? $orderData['pw_gift_cards_redeemed'][0]['amount'] : 0,

                  'date' => $orderData['date_created'],
                  'total_tax_order' => $orderData['total_tax'],
                  'total_order' => $orderData['total'],
                  'user_id' => $userId,
                  'status' => $orderData['status'],
                  'shipping_method' => isset($orderData['shipping_lines'][0]['method_id']) ? $orderData['shipping_lines'][0]['method_id'] : null,
                  'shipping_method_detail' => isset($orderData['shipping_lines'][0]['method_title']) ? $orderData['shipping_lines'][0]['method_title'] : null,
                  'shipping_amount' => isset($orderData['shipping_lines'][0]['total']) ? $orderData['shipping_lines'][0]['total'] : null,

                  'product_code' => $productCode,
                  'pick_up_location_id' => $pickUpLocationId,
               ];
               

               foreach($orderData['line_items'] as $value){

                  $productsToInsert[] = [
                     'order_id' => $orderData['id'],
                     'product_woocommerce_id' => $value['variation_id'] != 0 ? $value['variation_id'] : $value['product_id'],
                     'category' =>  isset($value['category'][0]['name']) ? $value['category'][0]['name'] : '',
                     'category_id' => isset($value['category'][0]['term_id']) ? $value['category'][0]['term_id'] : '',
                     'quantity' => $value['quantity'],
                     'cost' => $value['subtotal'] / $value['quantity'],
                     'subtotal_tax' =>  $value['subtotal_tax'],
                     'total_tax' =>  $value['total_tax'],
                     'total_price' => $value['total'],
                     'pick' => 0,
                     'line_item_id' => $value['id'],
                     'pick_control' => 0
                  ];
               }
            }
         }

    
         // Insérer les données dans la base de données par lot
         try{
            $this->model->insert($ordersToInsert);
            DB::table('products_order')->insert($productsToInsert);
         } catch(Exception $e){ 
            dd($e->getMessage());
            continue;
         }

      }
      echo json_encode(['success' => true]);
   }

   public function getOrdersByUsers(){
      return $this->model->select('orders.*', 'users.name')->whereIn('orders.status', ['processing', 'waiting_to_validate', 'waiting_validate', 'order-new-distrib'])->join('users', 'users.id', '=', 'orders.user_id')->get();
   }

   public function getAllOrdersByUsersNotFinished(){
      return $this->model->select('orders.*', 'users.name')->where('status', '!=', 'finished')->join('users', 'users.id', '=', 'orders.user_id')->get();
   }

   public function getUsersWithOrder(){
      return $this->model->select('users.*')->whereIn('orders.status', ['processing', 'waiting_to_validate', 'waiting_validate', 'order-new-distrib'])->join('users', 'users.id', '=', 'orders.user_id')->groupBy('users.id')->get();
   }

   public function getAllOrdersByIdUser($user_id){
      return $this->model->select('*')->where('user_id', $user_id)->get();
   }

   public function getOrdersByIdUser($id, $distributeur_order = false){
      // Liste des distributeurs
      $distributeurs = DB::table('distributors')->select('customer_id')->get();
      $distributeurs_id = [];
      foreach($distributeurs as $distributeur){
         $distributeurs_id[] = $distributeur->customer_id;
      }

      $list = [];

      // Pour filtrer les gels par leurs attributs les 20 puis les 50 après
      $queryOrder = "CASE WHEN prepa_products.name LIKE '%20 ml' THEN prepa_categories.order_display ";
      $queryOrder .= "WHEN prepa_products.name LIKE '%50 ml' THEN prepa_categories.order_display+1 ";
      $queryOrder .= "ELSE prepa_categories.order_display END";

      $orders = 
      $this->model->join('products_order', 'products_order.order_id', '=', 'orders.order_woocommerce_id')
         ->Leftjoin('products', 'products.product_woocommerce_id', '=', 'products_order.product_woocommerce_id')
         ->join('categories', 'products_order.category_id', '=', 'categories.category_id_woocommerce')
         ->where('user_id', $id)
         ->whereIn('orders.status', ['processing', 'waiting_to_validate', 'waiting_validate', 'order-new-distrib'])
         ->select('orders.*', 'products.product_woocommerce_id', 'products.category', 'products.category_id', 'products.variation',
         'products.name', 'products.barcode', 'products.location', 'categories.order_display', 'products_order.pick', 'products_order.quantity',
         'products_order.subtotal_tax', 'products_order.total_tax','products_order.total_price', 'products_order.cost', 'products.weight')
         ->orderBy('orders.date', 'ASC')
         ->orderByRaw($queryOrder)
         ->orderBy('categories.order_display', 'ASC')
         ->orderBy('products.menu_order', 'ASC')
         ->get();

      $orders = json_decode(json_encode($orders), true);
  

      foreach($orders as $key => $order){
         if($distributeur_order){
            if(in_array($order['customer_id'], $distributeurs_id) || $order['status'] == "order-new-distrib"){
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
                  'gift_card_amount' => $order['gift_card_amount'],
                  'shipping_amount' => $order['shipping_amount']
               ];
               $list[$order['order_woocommerce_id']]['items'][] = $order;
            }
         } else {
            if(!in_array($order['customer_id'], $distributeurs_id) && $order['status'] != "order-new-distrib"){
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
                  'gift_card_amount' => $order['gift_card_amount'],
                  'shipping_amount' => $order['shipping_amount'],
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

   public function checkIfDone($order_id, $barcode_array, $products_quantity, $partial = false) {

      $list_product_orders = DB::table('products')
      ->select('barcode', 'products_order.quantity', 'products_order.id')
      ->join('products_order', 'products_order.product_woocommerce_id', '=', 'products.product_woocommerce_id')
      ->where('products_order.order_id', $order_id)
      ->get()
      ->toArray();

    
      $list_product_orders = json_decode(json_encode($list_product_orders), true);
      $product_pick_in = [];

      // Construit le tableaux à update 
      $barcode_research = array_column($list_product_orders, "barcode");
      foreach($barcode_array as $key => $barcode){
         $clesRecherchees = array_keys($barcode_research, $barcode);
         $product_pick_in[] = [
            'id' => $list_product_orders[$clesRecherchees[0]]['id'],
            'barcode' => $barcode,
            'quantity' => intval($products_quantity[$key])
         ];
      }

      // Récupère les différences entre les produits de la commande et ceux qui ont été bippés
      $barcode = array_column($product_pick_in, "barcode");
      $diff_quantity = false;
      $diff_barcode = false;

      foreach($list_product_orders as $list){
         $clesRecherchees = array_keys($barcode, $list['barcode']);
         if(count($clesRecherchees) != 0){
            if($product_pick_in[$clesRecherchees[0]]['quantity'] != $list['quantity']){
               $diff_quantity = true;
            }
         } else {
            $diff_barcode = true;
         }
      }

      // Mise à jour de la valeur pick avec la quantité qui a été bippé pour chaque produit
      $cases = collect($product_pick_in)->map(function ($item) {
         return sprintf("WHEN %d THEN '%s'", $item['id'], intval($item['quantity']));
      })->implode(' ');

      $query = "UPDATE prepa_products_order SET pick = (CASE id {$cases} END)";
      DB::statement($query);

      if(!$partial){
         if(!$diff_quantity && !$diff_barcode){
            // Modifie le status de la commande en "Commande préparée",
            $update_status_local = $this->updateOrdersById([$order_id], "prepared-order");
   
            // Insert la commande dans histories
            DB::table('histories')->insert([
               'order_id' => $order_id,
               'user_id' => Auth()->user()->id,
               'status' => 'prepared',
               'created_at' => date('Y-m-d H:i:s')
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

   public function checkIfValidDone($order_id, $barcode_array, $products_quantity){

      $list_product_orders = DB::table('products')
      ->select('barcode', 'products_order.quantity', 'products_order.id')
      ->join('products_order', 'products_order.product_woocommerce_id', '=', 'products.product_woocommerce_id')
      ->where('products_order.order_id', $order_id)
      ->get()
      ->toArray();

      $list_product_orders = json_decode(json_encode($list_product_orders), true);
      $product_pick_in = [];

      // Construit le tableaux à update 
      $barcode_research = array_column($list_product_orders, "barcode");
      foreach($barcode_array as $key => $barcode){
         $clesRecherchees = array_keys($barcode_research, $barcode);
         $product_pick_in[] = [
            'id' => $list_product_orders[$clesRecherchees[0]]['id'],
            'barcode' => $barcode,
            'quantity' => intval($products_quantity[$key])
         ];
      }

      // Récupère les différences entre les produits de la commande et ceux qui ont été bippés
      $barcode = array_column($product_pick_in, "barcode");
      $diff_quantity = false;
      $diff_barcode = false;

      foreach($list_product_orders as $list){
         $clesRecherchees = array_keys($barcode, $list['barcode']);
         if(count($clesRecherchees) != 0){
            if($product_pick_in[$clesRecherchees[0]]['quantity'] != $list['quantity']){
               $diff_quantity = true;
            }
         } else {
            $diff_barcode = true;
         }
      }


      // Mise à jour de la valeur pick avec la quantité qui a été bippé pour chaque produit
      $cases = collect($product_pick_in)->map(function ($item) {
         return sprintf("WHEN %d THEN '%s'", $item['id'], intval($item['quantity']));
      })->implode(' ');

      $query = "UPDATE prepa_products_order SET pick_control = (CASE id {$cases} END)";
      DB::statement($query);

      if($diff_barcode && $diff_quantity){
         return true;
      } else {
         return false;
      }

   }

   public function orderReset($order_id) {
      try{
         $update_products = DB::table('products_order')->where('order_id', $order_id)->update(['pick' => 0]);
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
               $pickUpLocationId = $this->getValueByKey($insert_order_by_user['meta_data'], "_lpc_meta_pickUpLocationId");


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

                  'payment_method' => $insert_order_by_user['payment_method'] ? $insert_order_by_user['payment_method'] : (count($insert_order_by_user['pw_gift_cards_redeemed']) > 0 ? 'gift_card' : null ),
                  'payment_method_title' => $insert_order_by_user['payment_method_title'] ? $insert_order_by_user['payment_method_title'] : (count($insert_order_by_user['pw_gift_cards_redeemed']) > 0 ? 'Gift Card' : null ),
                  'gift_card_amount' => isset($insert_order_by_user['pw_gift_cards_redeemed'][0]['amount']) ? $insert_order_by_user['pw_gift_cards_redeemed'][0]['amount'] : 0,

                  'date' => $insert_order_by_user['date_created'],
                  'total_tax_order' => $insert_order_by_user['total_tax'],
                  'total_order' => $insert_order_by_user['total'],
                  'user_id' => $user_id,
                  'status' => $insert_order_by_user['status'],
                  'shipping_method' => isset($insert_order_by_user['shipping_lines'][0]['method_id']) ? $insert_order_by_user['shipping_lines'][0]['method_id'] : null,
                  'shipping_method_detail' => isset($insert_order_by_user['shipping_lines'][0]['method_title']) ? $insert_order_by_user['shipping_lines'][0]['method_title'] : null,
                  'shipping_amount' => isset($insert_order_by_user['shipping_lines'][0]['total']) ? $insert_order_by_user['shipping_lines'][0]['total'] : null,
                  'product_code' => $productCode,
                  'pick_up_location_id' => $pickUpLocationId
               ];

               // Insert produits
               foreach($insert_order_by_user['line_items'] as $value){
                  $productsToInsert[] = [
                     'order_id' => $insert_order_by_user['id'],
                     'product_woocommerce_id' => $value['variation_id'] != 0 ? $value['variation_id'] : $value['product_id'],
                     'category' =>  isset($value['category'][0]['name']) ? $value['category'][0]['name'] : '',
                     'category_id' => isset($value['category'][0]['term_id']) ? $value['category'][0]['term_id'] : '',
                     'quantity' => $value['quantity'],
                     'cost' => $value['subtotal'] / $value['quantity'],
                     'subtotal_tax' =>  $value['subtotal_tax'],
                     'total_tax' =>  $value['total_tax'],
                     'total_price' => $value['total'],
                     'pick' => 0,
                     'line_item_id' => $value['id'],
                     'pick_control' => 0
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
      return $this->model::select('orders.*', 'products_order.pick', 'products_order.pick_control', 'products_order.quantity',
      'products_order.subtotal_tax', 'products_order.total_tax','products_order.total_price', 'products_order.cost', 'products.weight',
      'products.name', 'products.price', 'products.barcode', 'products.manage_stock', 'products.stock', 'products_order.product_woocommerce_id',
      'products.variation')
      ->where('order_woocommerce_id', $order_id)
      ->join('products_order', 'products_order.order_id', '=', 'orders.order_woocommerce_id')
      ->join('products', 'products.product_woocommerce_id', '=', 'products_order.product_woocommerce_id')
      ->get()
      ->toArray();
   }

   public function getOrderByIdWithCustomer($order_id){
      return $this->model::select('orders.*', 'products_order.pick', 'products_order.pick_control', 'products_order.quantity',
      'products_order.subtotal_tax', 'products_order.total_tax','products_order.total_price', 'products_order.cost', 'products.weight',
      'products.name', 'products.price', 'products.barcode', 'products.manage_stock', 'products.stock', 'products_order.product_woocommerce_id',
      'products.variation', 'distributors.customer_id as is_distributor', 'users.name as preparateur')
      ->where('order_woocommerce_id', $order_id)
      ->join('products_order', 'products_order.order_id', '=', 'orders.order_woocommerce_id')
      ->join('products', 'products.product_woocommerce_id', '=', 'products_order.product_woocommerce_id')
      ->join('users', 'orders.user_id', '=', 'users.id')
      ->leftJoin('distributors', 'distributors.customer_id', '=', 'orders.customer_id')
      ->get()
      ->toArray();
   }

   public function getAllOrdersAndLabel(){
      return $this->model::select('orders.*', 'label_product_order.*', 'labels.tracking_number', 'labels.created_at as label_created_at')
      ->Leftjoin('label_product_order', 'label_product_order.order_id', '=', 'orders.order_woocommerce_id')
      ->Leftjoin('labels', 'labels.id', '=', 'label_product_order.label_id')
      ->get();
   }
   
   public function getHistoryByUser($user_id){
  
      $list = [];

      // Pour filtrer les gels par leurs attributs les 20 puis les 50 après
      $queryOrder = "CASE WHEN prepa_products.name LIKE '%20 ml' THEN prepa_categories.order_display ";
      $queryOrder .= "WHEN prepa_products.name LIKE '%50 ml' THEN prepa_categories.order_display+1 ";
      $queryOrder .= "ELSE prepa_categories.order_display END";

      $orders = 
      $this->model->join('products_order', 'products_order.order_id', '=', 'orders.order_woocommerce_id')
         ->Leftjoin('products', 'products.product_woocommerce_id', '=', 'products_order.product_woocommerce_id')
         ->join('categories', 'products_order.category_id', '=', 'categories.category_id_woocommerce')
         ->where('user_id', $user_id)
         ->whereIn('orders.status', ['prepared-order'])
         ->select('orders.*', 'products.product_woocommerce_id', 'products.category', 'products.category_id', 'products.variation',
         'products.name', 'products.barcode', 'products.location', 'categories.order_display', 'products_order.pick', 'products_order.quantity',
         'products_order.subtotal_tax', 'products_order.total_tax','products_order.total_price', 'products_order.cost', 'products.weight')
         ->orderBy('orders.date', 'ASC')
         ->orderByRaw($queryOrder)
         ->orderBy('categories.order_display', 'ASC')
         ->orderBy('products.menu_order', 'ASC')
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
            'gift_card_amount' => $order['gift_card_amount'],
            'shipping_amount' => $order['shipping_amount'],
         ];
         $list[$order['order_woocommerce_id']]['items'][] = $order;
      }
 
      $list = array_values($list);
      return $list;
   }

   public function getAllHistory(){
      $list = [];

      // Pour filtrer les gels par leurs attributs les 20 puis les 50 après
      $queryOrder = "CASE WHEN prepa_products.name LIKE '%20 ml' THEN prepa_categories.order_display ";
      $queryOrder .= "WHEN prepa_products.name LIKE '%50 ml' THEN prepa_categories.order_display+1 ";
      $queryOrder .= "ELSE prepa_categories.order_display END";

      $orders = 
      $this->model->join('products_order', 'products_order.order_id', '=', 'orders.order_woocommerce_id')
         ->Leftjoin('products', 'products.product_woocommerce_id', '=', 'products_order.product_woocommerce_id')
         ->join('categories', 'products_order.category_id', '=', 'categories.category_id_woocommerce')
         ->join('users', 'users.id', '=', 'orders.user_id')
         ->whereIn('orders.status', ['prepared-order'])
         ->select('orders.*', 'users.name as preparateur','products.product_woocommerce_id', 'products.category', 'products.category_id', 'products.variation',
         'products.name', 'products.barcode', 'products.location', 'categories.order_display', 'products_order.pick', 'products_order.quantity',
         'products_order.subtotal_tax', 'products_order.total_tax','products_order.total_price', 'products_order.cost', 'products.weight')
         ->orderBy('orders.date', 'ASC')
         ->orderByRaw($queryOrder)
         ->orderBy('categories.order_display', 'ASC')
         ->orderBy('products.menu_order', 'ASC')
         ->get();

       $orders = json_decode(json_encode($orders), true);

       foreach($orders as $key => $order){
            $list[$order['order_woocommerce_id']]['preparateur'] =  $order['preparateur'];
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
               'gift_card_amount' => $order['gift_card_amount'],
               'shipping_amount' => $order['shipping_amount'],
            ];
            $list[$order['order_woocommerce_id']]['items'][] = $order;
      }
 
      $list = array_values($list);
      return $list;
   }


   // Fonction pour récupérer la valeur avec une clé spécifique
   private function getValueByKey($array, $key) {
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

   public function updateTotalOrder($order_id, $data){
      return $this->model->where('order_woocommerce_id', $order_id)->update([
         'total_tax_order' =>  $data['total_tax'],
         'total_order' =>  $data['total']
      ]);
   }

   public function getProductOrder($order_id){
      return $this->model::select('products.name', 'products.weight', 
         'products_order.quantity', 'products_order.product_woocommerce_id', 'products_order.cost')
         ->join('products_order', 'products_order.order_id', '=', 'orders.order_woocommerce_id')
         ->join('products', 'products.product_woocommerce_id', '=', 'products_order.product_woocommerce_id')
         ->where('orders.order_woocommerce_id', $order_id)
         ->get();
   }
}























