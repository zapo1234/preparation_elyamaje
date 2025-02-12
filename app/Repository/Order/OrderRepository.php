<?php

namespace App\Repository\Order;

use Exception;
use App\Models\Order;
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


   public function insertOrdersByUsers($array_user, $distributors_list = []){

      // Discount code for connect 
      $code = [];

      // Parcourir les données des utilisateurs
      foreach ($array_user as $userId => $userOrders) {
            $ordersToInsert = [];
            $productsToInsert = [];
       
            // Construire un tableau des données d'insertion pour l'utilisateur actuel
            foreach ($userOrders as $orderData) {
               $is_distributor = false;
               if(!isset($orderData['from_dolibarr'])){
                  // Récupérer que les commandes venant de woocommerce, les autres sont déjà en base pas besoin de réinsérer
                  $coupons = [];
                  $discount = [];
                  $amount = [];
                  
                  // Total products for each orders
                  $quantity = 0;

                  if(isset($orderData['coupon_lines'])){
                     foreach($orderData['coupon_lines'] as $coupon){
                        $coupons[] = $coupon['code'];
                        $discount[] = $coupon['discount'];
                        $amount[] = isset($coupon['nominal_amount']) ? $coupon['nominal_amount'] : 0;
                     }
                  }
                  
                  // Get all discounts codes live / student (amb / -10)
                  if(count($coupons) > 0){
                     $discount_code = [];
                     foreach($coupons as $cp){
                        $check_students_code = isset(explode('-', $cp)[3]) ? explode('-', $cp)[3] : false;
                        if(str_contains($cp, 'amb') || $check_students_code == "10"){
                           $discount_code[] = $cp;
                        }
                     }
                     if(count($discount_code) > 0){
                        $code [] = [
                           "order_id"        => $orderData['id'],
                           "first_name"      => $orderData['billing']['first_name'] ?? "",
                           "last_name"       => $orderData['billing']['last_name'] ?? "" ,
                           "phone"           => $orderData['billing']['phone'] ?? "" ,
                           "email"           => $orderData['billing']['email'] ?? "",
                           "address_1"       => $orderData['billing']['address_1'] ?? "",
                           "address_2"       => $orderData['billing']['address_2'] ?? "",
                           "city"            => $orderData['billing']['city'] ?? "",
                           "code"            => implode(',', $discount_code),
                           "total_ht"        => floatval($orderData['total'] - $orderData['total_tax']),
                           "total_ttc"       => floatval($orderData['total']),
                           'shipping_amount' => isset($orderData['shipping_lines'][0]['total']) ? floatval($orderData['shipping_lines'][0]['total']) : null,
                           'payment_method'  => $orderData['payment_method'] ? $orderData['payment_method'] : (count($orderData['pw_gift_cards_redeemed']) > 0 ? 'gift_card' : null ),
                           "status"          => $orderData['status'],
                           "status_updated"  => $orderData['date_created'],
                           "order_date"      => $orderData['date_created'],
                           'gift_card_amount' => isset($orderData['pw_gift_cards_redeemed'][0]['amount']) ? $orderData['pw_gift_cards_redeemed'][0]['amount'] : 0,
                           'refunds_amount'   => 0
                        ]; 
                     }
                  }
                
                  // Utilisation de la fonction pour récupérer la valeur avec la clé "_lpc_meta_pickUpProductCode"
                  if(isset($orderData['meta_data'])){
                     $productCode = $this->getValueByKey($orderData['meta_data'], "_lpc_meta_pickUpProductCode");
                     $pickUpLocationId = $this->getValueByKey($orderData['meta_data'], "_lpc_meta_pickUpLocationId") ?? $this->getValueByKey($orderData['meta_data'], "_shipping_method_chronorelais", "chrono");
                     $is_professional = $this->getValueByKey($orderData['meta_data'], "billing_customer_is_professional");
                  } else {
                     $productCode = null;
                     $pickUpLocationId = null;
                     $is_professional = false;
                  }
                  
                  if(isset($orderData['cart_hash'])){
                     // Insert produits
                     $total_order = floatval($orderData['total']) + floatval(isset($orderData['pw_gift_cards_redeemed'][0]['amount']) ? $orderData['pw_gift_cards_redeemed'][0]['amount'] : 0);
                     
                     // Limit total gift 20
                     $total_gift = 0;
                     $max_gift = 20; 

                     foreach($orderData['line_items'] as $key => $value) {
                        // Check for gift card
                        $is_virtual = $value['is_virtual'] != "yes" && !str_contains($value['name'], 'Carte Cadeau') ? false : true;
                        if (!$is_virtual || ($is_virtual && count($orderData['line_items']) != 1)){
                           // if(($value['total'] == "0.00" || intval($value['total']) == 0) && (str_contains(strtolower($value['name']), 'vernis semi permanent'))){
                           //    if($total_gift == 0){
                           //       if(intval($value['quantity']) >= $max_gift){
                           //          $value['quantity'] = $max_gift;
                           //          $total_gift = $max_gift;
                           //       } else {
                           //          $total_gift = $total_gift + intval($value['quantity']);
                           //       }
                           //    } else {
                           //       if(intval($value['quantity'] + $total_gift) >= $max_gift){
                           //          $value['quantity'] = $max_gift - $total_gift;
                           //          $total_gift = $max_gift;
                           //       } else {
                           //          $total_gift = intval($total_gift + $value['quantity']);
                           //       }
                           //       if(intval($value['quantity']) <= 0){
                           //          $total_gift = $max_gift + 1;
                           //       }  
                           //    }

                           //    if($total_gift <= $max_gift) {
                           //       $quantity = intval($value['quantity']) + $quantity;
                           //       $productsToInsert[] = [
                           //          'order_id' => $orderData['id'],
                           //          'product_woocommerce_id' => $value['variation_id'] != 0 ? $value['variation_id'] : $value['product_id'],
                           //          'category' =>  isset($value['category'][0]['name']) ? $value['category'][0]['name'] : '',
                           //          'category_id' => isset($value['category'][0]['term_id']) ? $value['category'][0]['term_id'] : '',
                           //          'quantity' => $value['quantity'],
                           //          'cost' => $value['subtotal'] / $value['quantity'],
                           //          'subtotal_tax' =>  $value['subtotal_tax'],
                           //          'total_tax' =>  $value['total_tax'],
                           //          'total_price' => $value['total'],
                           //          'pick' => $is_virtual ? $value['quantity'] : 0,
                           //          'line_item_id' => $value['id'],
                           //          'pick_control' => 0
                           //       ];
                           //    }
                           // } else {
                              $quantity = intval($value['quantity']) + $quantity;
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
                                 'pick' => $is_virtual ? $value['quantity'] : 0,
                                 'line_item_id' => $value['id'],
                                 'pick_control' => 0
                              ];
                           // }
                        }
                     }

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
                        'total_products' => $quantity,
                        'user_id' => $userId,
                        'status' => $orderData['status'],
                        'shipping_method' => isset($orderData['shipping_lines'][0]['method_id']) ? $orderData['shipping_lines'][0]['method_id'] : null,
                        'shipping_method_detail' => isset($orderData['shipping_lines'][0]['method_title']) ? $orderData['shipping_lines'][0]['method_title'] : null,
                        'shipping_amount' => isset($orderData['shipping_lines'][0]['total']) ? floatval($orderData['shipping_lines'][0]['total']) : null,
                        'product_code' => $productCode,
                        'pick_up_location_id' => $pickUpLocationId,
                        'customer_note' => $orderData['customer_note'],
                        'is_professional' => $is_professional == "1" || $is_professional == 1 ? 1 : 0
                     ];
                     
                     if(in_array($orderData['customer_id'], $distributors_list)){
                        $is_distributor = true;
                     }
                     
                     // IF distributor we add 10 bags for 1000 euros
                     $total_ht = floatval($orderData['total'] - $orderData['total_tax']);
                     if($is_distributor && $total_ht >= 500){
                        $palier = [500 => 10, 900 => 30, 2000 => 60, 3000 => 90];
                        $nbr_sac = 0; 

                        // Check palier
                        foreach($palier as $key => $val){
                           if($total_ht >= $key){
                              $nbr_sac = $val;
                           }
                        }

                        // $montant_par_tranche = 500;
                        // $nbr_sac = floor($total_order / $montant_par_tranche) * 10;

                        $productsToInsert[] = [
                           'order_id' => $orderData['id'],
                           'product_woocommerce_id' => 110203,
                           'category' =>  '',
                           'category_id' => '',
                           'quantity' => $nbr_sac > 100 ? 100 : $nbr_sac,
                           'cost' => 0,
                           'subtotal_tax' =>  0,
                           'total_tax' =>  0,
                           'total_price' => 0,
                           'pick' => 0,
                           'line_item_id' => $orderData['id'].''.time(),
                           'pick_control' => 0
                        ];
                     }
                  }
               } else {
                  DB::table('orders_doli')->where('ref_order', $orderData['id'])->update(['user_id' => $userId]);
               }
            } 

            // Insérer les données dans la base de données par lot
            try{
               DB::transaction(function () use ($ordersToInsert, $productsToInsert, $code) {
                  DB::table('orders')->insertOrIgnore($ordersToInsert);
                  DB::table('products_order')->insertOrIgnore($productsToInsert);

                  // Insert into discount code for connect
                  if(count($code) > 0){
                     DB::table('discount_code')->insertOrIgnore($code);
                  }
               });
            } catch(Exception $e){ 
               echo json_encode(['success' => false]);
            }
      }
      echo json_encode(['success' => true]);
   }

   public function getOrdersByUsers(){
      return $this->model->select('orders.*', 'users.name')->whereIn('orders.status', ['en-attente-de-pai', 'processing', 'waiting_to_validate', 'waiting_validate', 'order-new-distrib'])->join('users', 'users.id', '=', 'orders.user_id')->get();
   }

   // With products
   public function getAllOrdersByUsersNotFinished(){
      return $this->model->select('orders.*', 'users.name')->where('status', '!=', 'finished')
      ->Leftjoin('users', 'users.id', '=', 'orders.user_id')
      // ->join('products_order', 'products_order.order_id', '=', 'orders.order_woocommerce_id')
      ->get();
   }

   // Without products
   public function getAllOrdersNotFinished(){
      // Get - 2 months
      $date = date('Y-m-d H:i:s', strtotime('-2 months'));
      return $this->model->select('orders.order_woocommerce_id')->where('date', '>', $date)->get();
   }

   public function getUsersWithOrder(){
      return $this->model->select('users.*')->whereIn('orders.status', ['en-attente-de-pai', 'processing', 'waiting_to_validate', 'waiting_validate', 'order-new-distrib'])->join('users', 'users.id', '=', 'orders.user_id')->groupBy('users.id')->get();
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
      $orders_count['distrib'] = 0;
      $orders_count['order'] = 0;
      $temporaly_array = [];

      // Pour filtrer les gels par leurs attributs les 20 puis les 50 après
      $queryOrder = "CASE WHEN prepa_products.name LIKE '%20ml%' THEN prepa_categories.order_display ";
      $queryOrder .= "WHEN prepa_products.name LIKE '%50ml%' THEN prepa_categories.order_display+1 ";
      $queryOrder .= "ELSE prepa_categories.order_display END";

      $orders = 
      $this->model->join('products_order', 'products_order.order_id', '=', 'orders.order_woocommerce_id')
         ->Leftjoin('products', 'products.product_woocommerce_id', '=', 'products_order.product_woocommerce_id')
         ->Leftjoin('categories', 'products_order.category_id', '=', 'categories.category_id_woocommerce')
         ->where('user_id', $id)
         ->whereIn('orders.status', ['en-attente-de-pai', 'processing', 'waiting_to_validate', 'waiting_validate', 'order-new-distrib'])
         ->select('orders.*', 'products.product_woocommerce_id', 'products.category', 'products.category_id', 'products.variation',
         'products.name', 'products_order.product_woocommerce_id as productID', 'products.barcode', 'products.location', 'categories.order_display', 'products_order.pick','products_order.quantity',
         'products_order.subtotal_tax', 'products_order.total_tax','products_order.total_price', 'products_order.cost', 'products.weight')
         ->orderByRaw("CASE WHEN prepa_orders.shipping_method LIKE '%chrono%' THEN 0 ELSE 1 END")
         ->orderBy('orders.date', 'ASC')
         ->orderByRaw($queryOrder)
         ->orderBy('categories.order_display', 'ASC')
         ->orderBy('products.menu_order', 'ASC')
         ->get();

      $orders = json_decode(json_encode($orders), true);

      foreach($orders as $key => $order){
         if($distributeur_order){
            if(in_array($order['customer_id'], $distributeurs_id) || $order['status'] == "order-new-distrib"){
               // Comptabilise le nombre de commande 
               if(!isset($list[$order['order_woocommerce_id']])){
                  $orders_count['distrib'] = $orders_count['distrib'] + 1;
               }
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
                  'shipping_method' => $order['shipping_method'],
                  'customer_note'   => $order['customer_note']
               ];
               $list[$order['order_woocommerce_id']]['items'][] = $order;
            } else {
               if(!isset($temporaly_array[$order['order_woocommerce_id']])){
                  $orders_count['order'] = $orders_count['order'] + 1;
               }
               $temporaly_array[$order['order_woocommerce_id']] = 0;
            }
         } else {
            if(!in_array($order['customer_id'], $distributeurs_id) && $order['status'] != "order-new-distrib"){
               // Comptabilise le nombre de commande 
               if(!isset($list[$order['order_woocommerce_id']])){
                  $orders_count['order'] = $orders_count['order'] + 1;
               }
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
                  'shipping_method' => $order['shipping_method'],
                  'customer_note'   => $order['customer_note']
               ];
               $list[$order['order_woocommerce_id']]['items'][] = $order;
               
            } else {
               if(!isset($temporaly_array[$order['order_woocommerce_id']])){
                  $orders_count['distrib'] = $orders_count['distrib'] + 1;
               }
               $temporaly_array[$order['order_woocommerce_id']] = 0;
               
            }
         }
      }

      // Cas de produits double si par exemple 1 en cadeau et 1 normal
      $product_double = [];
      foreach($list as $key1 => $li){
         foreach($li['items'] as $key2 => $item){
            if(isset($product_double[$key1])){

              $id_product = array_column($product_double[$key1], "id");
              $clesRecherchees = array_keys($id_product,  $item['product_woocommerce_id']);

              if(count($clesRecherchees) > 0){
                  $detail_doublon = $product_double[$key1][$clesRecherchees[0]];
                  unset($list[$key1]['items'][$key2]);

                  // Merge quantity
                  $list[$detail_doublon['key1']]['items'][$detail_doublon['key2']]['quantity'] = $item['quantity'] + $detail_doublon['quantity'];
               
                  // Merge pick product
                  $list[$detail_doublon['key1']]['items'][$detail_doublon['key2']]['pick'] = $item['pick'] + $detail_doublon['pick'];
              } else {
                  $product_double[$key1][] = [
                     'id' => $item['product_woocommerce_id'],
                     'quantity' => $item['quantity'], 
                     'key1' => $key1,
                     'key2' => $key2,
                     'pick' => $item['pick']
                 ];
              }
            } else {
               $product_double[$key1][] = [
                  'id' => $item['product_woocommerce_id'],
                  'quantity' => $item['quantity'], 
                  'key1' => $key1,
                  'key2' => $key2,
                  'pick' => $item['pick']
               ];
            }
         }
      }
      
      $list = array_values($list);
      return ['orders' => $list, 'count' => $orders_count];
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
         ->select(DB::raw('REPLACE(barcode, " ", "") AS barcode'), 'products_order.quantity', 'products_order.id', 'products_order.pick')
         ->join('products_order', 'products_order.product_woocommerce_id', '=', 'products.product_woocommerce_id')
         ->where('products_order.order_id', $order_id)
         ->get()
         ->toArray();

      $list_product_orders = json_decode(json_encode($list_product_orders), true);

      $total_product = 0;
      foreach($products_quantity as $product){
         $total_product = $total_product + intval($product);
      }

      // Tout est bippé donc on valide
      if(count($list_product_orders) == 0){
         try{
            // Insert la commande dans histories
            DB::table('histories')->insert([
               'order_id' => $order_id,
               'user_id' => Auth()->user()->id,
               'status' => 'prepared',
               'created_at' => date('Y-m-d H:i:s'),
               'total_product' => $total_product ?? null
            ]);

            $this->updateOrdersById([$order_id], "prepared-order");
            $this->api->updateOrdersWoocommerce("prepared-order", $order_id);
            return true;
         } catch(Exception $e){
            return $e->getMessage();
         }
      }

      // Cas de produits double si par exemple 1 en cadeau et 1 normal
      $product_double = [];
      foreach($list_product_orders as $key_barcode => $list){

         if(isset($product_double[$list["barcode"]])){
            if(isset($product_double[$list["barcode"]][0])){

              $quantity = $product_double[$list["barcode"]][0]['quantity'];
              $key_barcode_to_remove = $product_double[$list["barcode"]][0]['key_barcode_to_remove'];

              unset($list_product_orders[$key_barcode_to_remove]);
              $list_product_orders[$key_barcode]['quantity'] = $list_product_orders[$key_barcode]['quantity'] + $quantity;
            }
         } else {
            $product_double[$list["barcode"]][] = [
              'quantity' => $list['quantity'],
              'key_barcode_to_remove' => $key_barcode
            ];
         }
      }

      // Reconstruis le tableaux sans trou dans les clés à cause du unset précédent
      $list_products = [];
      foreach($list_product_orders as $list){
         // Ignore les produits bippés
         if($list['quantity'] != $list['pick']){
            $list_products[] = [
               "barcode" => $list['barcode'],
               "quantity" =>  $list['quantity'],
               "id" =>  $list['id'],
            ];
         }
      }

      $product_pick_in = [];
      $lits_id = [];
      // Construit le tableaux à update 
      $barcode_research = array_column($list_products, "barcode");
      
      foreach($barcode_array as $key => $barcode){
         $clesRecherchees = array_keys($barcode_research, $barcode);
          if(count($clesRecherchees) > 0){
             $lits_id[] = $list_products[$clesRecherchees[0]]['id'];
             $product_pick_in[] = [
                'id' => $list_products[$clesRecherchees[0]]['id'],
                'barcode' => $barcode,
                'quantity' => intval($products_quantity[$key])
             ];
          }
      }


      // Récupère les différences entre les produits de la commande et ceux qui ont été bippés
      $barcode = array_column($product_pick_in, "barcode");
      $diff_quantity = false;
      $diff_barcode = false;

      foreach($list_products as $list){
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

      if(count($product_pick_in) > 0){
         $query = "UPDATE prepa_products_order SET pick = (CASE id {$cases} END) WHERE id IN (".implode(',',$lits_id).")";
         DB::statement($query);
      }

      if(!$partial){
         if(!$diff_quantity && !$diff_barcode){
            // Modifie le status de la commande en "Commande préparée",
            $update_status_local = $this->updateOrdersById([$order_id], "prepared-order");
   
            // Insert la commande dans histories
            DB::table('histories')->insert([
               'order_id' => $order_id,
               'user_id' => Auth()->user()->id,
               'status' => 'prepared',
               'created_at' => date('Y-m-d H:i:s'),
               'total_product' => $total_product ?? null
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

      // Cas de produits double si par exemple 1 en cadeau et 1 normal
      $product_double = [];
      foreach($list_product_orders as $key_barcode => $list){

         if(isset($product_double[$list["barcode"]])){
            if(isset($product_double[$list["barcode"]][0])){

               $quantity = $product_double[$list["barcode"]][0]['quantity'];
               $key_barcode_to_remove = $product_double[$list["barcode"]][0]['key_barcode_to_remove'];

               unset($list_product_orders[$key_barcode_to_remove]);
               $list_product_orders[$key_barcode]['quantity'] = $list_product_orders[$key_barcode]['quantity'] + $quantity;
            }
         } else {
            $product_double[$list["barcode"]][] = [
               'quantity' => $list['quantity'],
               'key_barcode_to_remove' => $key_barcode
            ];
         }
      }

      // Reconstruis le tableaux sans trou dans les clés à cause du unset précédent
      $list_products = [];
      foreach($list_product_orders as $list){
         $list_products[] = [
            "barcode" => $list['barcode'],
            "quantity" =>  $list['quantity'],
            "id" =>  $list['id'],
         ];
      }

      $product_pick_in = [];

      // Construit le tableaux à update 
      $barcode_research = array_column($list_products, "barcode");
      foreach($barcode_array as $key => $barcode){
         $clesRecherchees = array_keys($barcode_research, $barcode);
         $product_pick_in[] = [
            'id' => $list_products[$clesRecherchees[0]]['id'],
            'barcode' => $barcode,
            'quantity' => intval($products_quantity[$key])
         ];
      }

      // Récupère les différences entre les produits de la commande et ceux qui ont été bippés
      $barcode = array_column($product_pick_in, "barcode");
      $diff_quantity = false;
      $diff_barcode = false;

      foreach($list_products as $list){
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
         $update_order_attribution =  $this->model::where('user_id', $from_user)->whereIn('status', ['processing', 'en-attente-de-pai', 'order-new-distrib'])->update(['user_id' => $to_user]);
         return true;
      } catch(Exception $e){
         return false;
      }
   }

   public function updateMultipleOrderAttribution($array_user){
      try{
         // Woocommerce
         $woocommerceUpdate = "UPDATE prepa_orders SET user_id = ( CASE order_woocommerce_id";

         // Dolibarr
         $dolibarrUpdate = "UPDATE prepa_orders_doli SET user_id = ( CASE ref_order";

         foreach ($array_user as $key => $distribution) {
            // Traitement pour Woocommerce
            $woocommerce_order_ids = $distribution['woocommerce']['order_id'];
            $woocommerce_order_id_chunks = array_chunk($woocommerce_order_ids, 200);

            foreach ($woocommerce_order_id_chunks as $chunk) {
               $updateQueryChunk = $woocommerceUpdate;
               foreach ($chunk as $order_id) {
                  $updateQueryChunk .= " WHEN $order_id THEN $key ";
               }
               $updateQueryChunk .= " END) WHERE order_woocommerce_id IN ('" . implode("','", $chunk) . "')";
               DB::update(DB::raw($updateQueryChunk));
            }

            // Traitement pour Dolibarr
            $dolibarr_order_ids = $distribution['dolibarr']['order_id'];
            $dolibarr_order_id_chunks = array_chunk($dolibarr_order_ids, 100);

            foreach ($dolibarr_order_id_chunks as $chunk) {
               $updateQuery2Chunk = $dolibarrUpdate;
               foreach ($chunk as $order_id) {
                  $updateQuery2Chunk .= " WHEN '".$order_id."' THEN $key ";
               }


               $updateQuery2Chunk .= " END) WHERE ref_order IN ('" . implode("','", $chunk) . "')";
               DB::update(DB::raw($updateQuery2Chunk));

               sleep(1);
            }
         }
         return true;
      } catch(Exception $e){
         return $e->getMessage();
      }
   }

   public function updateOneOrderAttribution($order_id, $user_id, $is_distributor){

      /* ---------------- New function, just update user_id 0 if not assigned ---------------- */
      try{
         return $this->model::where('order_woocommerce_id', $order_id)->update(['user_id' => $user_id]);
      } catch(Exception $e){
         return $e->getMessage();
      }
      

      /* ---------------- Old function, create order or delete if not assigned ---------------- */
      // try{
      //    if($user_id == "Non attribuée"){
      //       $order = $this->model::where('order_woocommerce_id', $order_id)->delete();
      //       DB::table('products_order')->where('order_id', $order_id)->delete();
      //    } else {
      //       $order = $this->model::where('order_woocommerce_id', $order_id)->get()->toArray();
      //       if(count($order) == 0){
      //          $insert_order_by_user = $this->api->getOrderById($order_id);

      //          $coupons = [];
      //          $discount = [];
      //          $amount = [];

      //          if(isset($insert_order_by_user['coupon_lines'])){
      //             foreach($insert_order_by_user['coupon_lines'] as $coupon){
      //                $coupons[] = $coupon['code'];
      //                $discount[] = $coupon['discount'];
      //                $amount[] = isset($coupon['nominal_amount']) ? $coupon['nominal_amount'] : 0;
      //             }
      //          }

      //          // Utilisation de la fonction pour récupérer la valeur avec la clé "_lpc_meta_pickUpProductCode"
      //          if(isset($insert_order_by_user['meta_data'])){
      //             $productCode = $this->getValueByKey($insert_order_by_user['meta_data'], "_lpc_meta_pickUpProductCode");
      //             $pickUpLocationId = $this->getValueByKey($insert_order_by_user['meta_data'], "_lpc_meta_pickUpLocationId") ?? $this->getValueByKey($insert_order_by_user['meta_data'], "_shipping_method_chronorelais", "chrono");
      //             $is_professional = $this->getValueByKey($insert_order_by_user['meta_data'], "billing_customer_is_professional");
      //          } else {
      //             $productCode = null;
      //             $pickUpLocationId = null;
      //             $is_professional = false;
      //          }
             
      //          // Insert commande
      //          $ordersToInsert = [
      //             'order_woocommerce_id' => $insert_order_by_user['id'],
      //             'customer_id' => $insert_order_by_user['customer_id'],
      //             'coupons' => count($coupons) > 0 ? implode(',', $coupons) : "",
      //             'discount' => count($discount) > 0 ? implode(',', $discount) : 0,
      //             'discount_amount' => count($amount) > 0 ? implode(',', $amount) : 0,
      //             'billing_customer_first_name' => $insert_order_by_user['billing']['first_name'] ?? null,
      //             'billing_customer_last_name' => $insert_order_by_user['billing']['last_name'] ?? null,
      //             'billing_customer_company' => $insert_order_by_user['billing']['company'] ?? null,
      //             'billing_customer_address_1' => $insert_order_by_user['billing']['address_1'] ?? null,
      //             'billing_customer_address_2' => $insert_order_by_user['billing']['address_2'] ?? null,
      //             'billing_customer_city' => $insert_order_by_user['billing']['city'] ?? null,
      //             'billing_customer_state' => $insert_order_by_user['billing']['state'] ?? null,
      //             'billing_customer_postcode' => $insert_order_by_user['billing']['postcode'] ?? null,
      //             'billing_customer_country' => $insert_order_by_user['billing']['country'] ?? null,
      //             'billing_customer_email' => $insert_order_by_user['billing']['email'] ?? null,
      //             'billing_customer_phone' => $insert_order_by_user['billing']['phone'] ?? null,
      //             'shipping_customer_first_name' => $insert_order_by_user['shipping']['first_name'] ?? null,
      //             'shipping_customer_last_name' => $insert_order_by_user['shipping']['last_name'] ?? null,
      //             'shipping_customer_company' => $insert_order_by_user['shipping']['company'] ?? null,
      //             'shipping_customer_address_1' => $insert_order_by_user['shipping']['address_1'] ?? null,
      //             'shipping_customer_address_2' => $insert_order_by_user['shipping']['address_2'] ?? null,
      //             'shipping_customer_city' => $insert_order_by_user['shipping']['city'] ?? null,
      //             'shipping_customer_state' => $insert_order_by_user['shipping']['state'] ?? null,
      //             'shipping_customer_postcode' => $insert_order_by_user['shipping']['postcode'] ?? null,
      //             'shipping_customer_country' => $insert_order_by_user['shipping']['country'] ?? null,
      //             'shipping_customer_phone' => $insert_order_by_user['shipping']['phone'] ?? null,

      //             'payment_method' => $insert_order_by_user['payment_method'] ? $insert_order_by_user['payment_method'] : (count($insert_order_by_user['pw_gift_cards_redeemed']) > 0 ? 'gift_card' : null ),
      //             'payment_method_title' => $insert_order_by_user['payment_method_title'] ? $insert_order_by_user['payment_method_title'] : (count($insert_order_by_user['pw_gift_cards_redeemed']) > 0 ? 'Gift Card' : null ),
      //             'gift_card_amount' => isset($insert_order_by_user['pw_gift_cards_redeemed'][0]['amount']) ? $insert_order_by_user['pw_gift_cards_redeemed'][0]['amount'] : 0,

      //             'date' => $insert_order_by_user['date_created'],
      //             'total_tax_order' => $insert_order_by_user['total_tax'],
      //             'total_order' => $insert_order_by_user['total'],
      //             'user_id' => $user_id,
      //             'status' => $insert_order_by_user['status'],
      //             'shipping_method' => isset($insert_order_by_user['shipping_lines'][0]['method_id']) ? $insert_order_by_user['shipping_lines'][0]['method_id'] : null,
      //             'shipping_method_detail' => isset($insert_order_by_user['shipping_lines'][0]['method_title']) ? $insert_order_by_user['shipping_lines'][0]['method_title'] : null,
      //             'shipping_amount' => isset($insert_order_by_user['shipping_lines'][0]['total']) ? $insert_order_by_user['shipping_lines'][0]['total'] : null,
      //             'product_code' => $productCode,
      //             'pick_up_location_id' => $pickUpLocationId,
      //             'customer_note' => $insert_order_by_user['customer_note'],
      //             'is_professional' => $is_professional == "1" || $is_professional == 1 ? 1 : 0
      //          ];

      //          // Insert produits
      //          $total_order = floatval($insert_order_by_user['total']) + floatval(isset($insert_order_by_user['pw_gift_cards_redeemed'][0]['amount']) ? $insert_order_by_user['pw_gift_cards_redeemed'][0]['amount'] : 0);
      //          foreach($insert_order_by_user['line_items'] as $value){

      //             // Check for gift card
      //             $is_virtual = $value['is_virtual'] != "yes" && !str_contains($value['name'], 'Carte Cadeau') ? false : true;

      //             if (!$is_virtual || ($is_virtual && count($insert_order_by_user['line_items']) != 1)){
      //                $productsToInsert[] = [
      //                   'order_id' => $insert_order_by_user['id'],
      //                   'product_woocommerce_id' => $value['variation_id'] != 0 ? $value['variation_id'] : $value['product_id'],
      //                   'category' =>  isset($value['category'][0]['name']) ? $value['category'][0]['name'] : '',
      //                   'category_id' => isset($value['category'][0]['term_id']) ? $value['category'][0]['term_id'] : '',
      //                   'quantity' => $value['quantity'],
      //                   'cost' => $value['subtotal'] / $value['quantity'],
      //                   'subtotal_tax' =>  $value['subtotal_tax'],
      //                   'total_tax' =>  $value['total_tax'],
      //                   'total_price' => $value['total'],
      //                   'pick' =>  $is_virtual ? $value['quantity'] : 0,
      //                   'line_item_id' => $value['id'],
      //                   'pick_control' => 0
      //                ];
      //             }
      //          }

      //          // IF distributor add bag 30 for 1000 euros
      //          if($is_distributor == "true" && $total_order >= 1000){
      //             $montant_par_tranche = 1000;
      //             $nbr_sac = floor($total_order / $montant_par_tranche) * 30;
      //             $productsToInsert[] = [
      //                'order_id' => $order_id,
      //                'product_woocommerce_id' => 110203,
      //                'category' =>  '',
      //                'category_id' => '',
      //                'quantity' => $nbr_sac,
      //                'cost' => 0,
      //                'subtotal_tax' =>  0,
      //                'total_tax' =>  0,
      //                'total_price' => 0,
      //                'pick' => 0,
      //                'line_item_id' => $order_id.''.time(),
      //                'pick_control' => 0
      //             ];
      //          }

      //          $this->model->insert($ordersToInsert);
      //          DB::table('products_order')->insert($productsToInsert);

      //       } else {
      //          $update_one_order_attribution =  $this->model::where('order_woocommerce_id', $order_id)->update(['user_id' => $user_id]);
      //       }
      //    }
      //    return true;
      // } catch(Exception $e){
      //    return $e->getMessage();
      // }
   }

   public function getOrderById($order_id){
      return $this->model::select('orders.*', 'products_order.pick', 'products_order.pick_control', 'products_order.quantity',
      'products_order.subtotal_tax', 'products_order.total_tax','products_order.total_price', 'products_order.cost', 'products.weight',
      'products.name', 'products.price', 'products.barcode', 'products.manage_stock', 'products.stock', 'products_order.product_woocommerce_id',
      'products.variation', 'products.image', 'products.ref', 'users.name as preparateur', 'products_order.line_item_id', 'products_order.category', 'products_order.category_id')
      ->where('order_woocommerce_id', $order_id)
      ->Leftjoin('products_order', 'products_order.order_id', '=', 'orders.order_woocommerce_id')
      ->Leftjoin('products', 'products.product_woocommerce_id', '=', 'products_order.product_woocommerce_id')
      ->Leftjoin('users', 'orders.user_id', '=', 'users.id')
      ->get()
      ->toArray();
   }

   public function getOrderByIdWithoutProducts($order_id){
      return $this->model::select('orders.*', 'users.name as preparateur')
      ->where('order_woocommerce_id', $order_id)
      ->Leftjoin('users', 'orders.user_id', '=', 'users.id')
      ->get()
      ->toArray();
   }

   public function getOrderByIdWithCustomer($order_id){
      return $this->model::select('orders.*', 'products_order.pick', 'products_order.pick_control', 'products_order.quantity',
      'products_order.subtotal_tax', 'products_order.total_tax','products_order.total_price', 'products_order.cost', 'products.weight',
      'products.name', 'products.price', 'products.barcode', 'products.manage_stock', 'products.stock', 'products_order.product_woocommerce_id',
      'products.variation', 'products.ref', 'distributors.customer_id as is_distributor', 'users.name as preparateur', 'products_order.category', 'products_order.category_id')
      ->where('order_woocommerce_id', $order_id)
      ->Leftjoin('products_order', 'products_order.order_id', '=', 'orders.order_woocommerce_id')
      ->Leftjoin('products', 'products.product_woocommerce_id', '=', 'products_order.product_woocommerce_id')
      ->Leftjoin('users', 'orders.user_id', '=', 'users.id')
      ->Leftjoin('distributors', 'distributors.customer_id', '=', 'orders.customer_id')
      ->get()
      ->toArray();
   }

   public function getAllOrdersAndLabel(){
      $date = date('Y-m-d');
      return $this->model::select('orders.*', /*'label_product_order.*'*/ 'labels.tracking_number', 'labels.created_at as label_created_at', 'labels.label_format', 
      'labels.cn23', 'labels.download_cn23',  'labels.origin', 'labels.id as label_id')
      // ->Leftjoin('label_product_order', 'label_product_order.order_id', '=', 'orders.order_woocommerce_id')
      // ->Leftjoin('labels', 'labels.id', '=', 'label_product_order.label_id')
      ->Leftjoin('labels', 'labels.order_id', '=', 'orders.order_woocommerce_id')
      ->where('labels.created_at', 'LIKE', '%'.$date.'%')
      ->orderBy('labels.created_at', 'DESC')
      ->limit(100)
      ->get();
   }

   public function getAllOrdersAndLabelByFilter($filters){

      $query = DB::table('orders')->select('orders.*', /*'label_product_order.*',*/ 'labels.id as label_id', 'labels.tracking_number', 'labels.created_at as label_created_at', 'labels.label_format', 
      'labels.cn23', 'labels.download_cn23', 'labels.origin')
      // ->Leftjoin('label_product_order', 'label_product_order.order_id', '=', 'orders.order_woocommerce_id')
      ->Leftjoin('labels', 'labels.order_id', '=', 'orders.order_woocommerce_id');

     
      $haveFilter = false;
      foreach($filters as $key => $filter){
         if($filter){
            $haveFilter = true;
            if($key == "created_at"){
               $query->where("labels.".$key."","LIKE",  "%".$filter."%");
            } else if($key == "origin"){
               $query->where("labels.origin", $filter);
            } else if($key == "order_woocommerce_id"){
               if(strlen($filter) == 13 && !str_contains($filter, 'BP') && !str_contains($filter, 'CO') && !str_contains($filter, 'SAV') && !str_contains($filter, 'GAL')){
                  $query->where("labels.tracking_number", $filter);
               } else {
                  $query->where("orders.".$key."", $filter);
               }
            }
         }
      }

      if(!$haveFilter){
         $date = date('Y-m-d');
         $query->where("labels.created_at","LIKE",  "%".$date."%");
      }

      $query->groupBy('labels.tracking_number');
      $query->orderBy('labels.created_at', 'DESC');
      $query->limit(100);
      $results = $query->get();
      return $results;
   }
   
   public function getHistoryByUser($user_id){
  
      $list_orders = [];

      // Pour filtrer les gels par leurs attributs les 20 puis les 50 après
      $queryOrder = "CASE WHEN prepa_products.name LIKE '%20ml%' THEN prepa_categories.order_display ";
      $queryOrder .= "WHEN prepa_products.name LIKE '%50ml%' THEN prepa_categories.order_display+1 ";
      $queryOrder .= "ELSE prepa_categories.order_display END";

      $orders = 
      $this->model->join('products_order', 'products_order.order_id', '=', 'orders.order_woocommerce_id')
         ->Leftjoin('products', 'products.product_woocommerce_id', '=', 'products_order.product_woocommerce_id')
         ->Leftjoin('categories', 'products_order.category_id', '=', 'categories.category_id_woocommerce')
         ->where('user_id', $user_id)
         ->join('users', 'users.id', '=', 'orders.user_id')
         ->whereIn('orders.status', ['prepared-order'])
         ->select('orders.*', 'users.name as preparateur', 'products.product_woocommerce_id', 'products.category', 'products.category_id', 'products.variation',
         'products.name', 'products.barcode', 'products.location', 'categories.order_display', 'products_order.pick', 'products_order.quantity',
         'products_order.subtotal_tax', 'products_order.total_tax','products_order.total_price', 'products_order.cost', 'products.weight')
         ->orderBy('orders.date', 'ASC')
         ->orderByRaw($queryOrder)
         ->orderBy('categories.order_display', 'ASC')
         ->orderBy('products.menu_order', 'ASC')
         ->get();

      $orders = json_decode(json_encode($orders), true);

      // Cas de produits double si par exemple 1 en cadeau et 1 normal
      $product_double = [];
      foreach($orders as $key => $list){
        
         if(isset($product_double[$list['order_woocommerce_id']][$list["barcode"]])){
            $quantity = $product_double[$list['order_woocommerce_id']][$list["barcode"]]['quantity'];
            $key_barcode_to_remove = $product_double[$list['order_woocommerce_id']][$list["barcode"]]['key_barcode_to_remove'];
   
            unset($orders[$key_barcode_to_remove]);
            $orders[$key]['quantity'] = $orders[$key]['quantity'] + $quantity;
            $orders[$key]['pick'] = $orders[$key]['quantity'];
         } else {
            $product_double[$list['order_woocommerce_id']][$list["barcode"]] = [
               'quantity' => $list['quantity'],
               'key_barcode_to_remove' => $key
            ];
             
         }
      }

      // Reconstruis le tableaux sans trou dans les clés à cause du unset précédent
      foreach($orders as $order){
         $list_orders[$order['order_woocommerce_id']]['preparateur'] =  $order['preparateur'];
         $list_orders[$order['order_woocommerce_id']]['details'] = [
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
         $list_orders[$order['order_woocommerce_id']]['items'][] = $order;
      }

      $list_orders = array_values($list_orders);
      return $list_orders;
   }

   public function getAllHistory(){
      $list_orders = [];

      // Pour filtrer les gels par leurs attributs les 20 puis les 50 après
      $queryOrder = "CASE WHEN prepa_products.name LIKE '%20ml%' THEN prepa_categories.order_display ";
      $queryOrder .= "WHEN prepa_products.name LIKE '%50ml%' THEN prepa_categories.order_display+1 ";
      $queryOrder .= "ELSE prepa_categories.order_display END";

      $orders = 
      $this->model->join('products_order', 'products_order.order_id', '=', 'orders.order_woocommerce_id')
         ->Leftjoin('products', 'products.product_woocommerce_id', '=', 'products_order.product_woocommerce_id')
         ->Leftjoin('categories', 'products_order.category_id', '=', 'categories.category_id_woocommerce')
         ->join('users', 'users.id', '=', 'orders.user_id')
         ->whereIn('orders.status', ['prepared-order'])
         ->select('orders.*', 'users.name as preparateur','products.product_woocommerce_id', 'products.category', 'products.category_id', 'products.variation',
         'products.name', 'products.barcode', 'products.location', 'categories.order_display', 'products_order.pick', 'products_order.quantity',
         'products_order.subtotal_tax', 'products_order.total_tax','products_order.total_price', 'products_order.cost', 'products.weight')
         ->orderBy('orders.updated_at', 'DESC')
         ->orderByRaw($queryOrder)
         ->orderBy('categories.order_display', 'ASC')
         ->orderBy('products.menu_order', 'ASC')
         ->get();

      $orders = json_decode(json_encode($orders), true);

      // Cas de produits double si par exemple 1 en cadeau et 1 normal
      $product_double = [];
      foreach($orders as $key => $list){
        
         if(isset($product_double[$list['order_woocommerce_id']][$list["barcode"]])){
            $quantity = $product_double[$list['order_woocommerce_id']][$list["barcode"]]['quantity'];
            $key_barcode_to_remove = $product_double[$list['order_woocommerce_id']][$list["barcode"]]['key_barcode_to_remove'];
   
            unset($orders[$key_barcode_to_remove]);
            $orders[$key]['quantity'] = $orders[$key]['quantity'] + $quantity;
            $orders[$key]['pick'] = $orders[$key]['quantity'];
         } else {
            $product_double[$list['order_woocommerce_id']][$list["barcode"]] = [
               'quantity' => $list['quantity'],
               'key_barcode_to_remove' => $key
            ];
             
         }
      }

      // Reconstruis le tableaux sans trou dans les clés à cause du unset précédent
      foreach($orders as $order){
         $list_orders[$order['order_woocommerce_id']]['preparateur'] =  $order['preparateur'];
         $list_orders[$order['order_woocommerce_id']]['details'] = [
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
         $list_orders[$order['order_woocommerce_id']]['items'][] = $order;
      }

      $list_orders = array_values($list_orders);
      return $list_orders;
   }


   // Fonction pour récupérer la valeur avec une clé spécifique
   private function getValueByKey($array, $key, $type = false) {
       foreach ($array as $item) {
         if ($item['key'] === $key) {
            if($type == "chrono"){
               return $item['value']['id'];
            } else {
               return $item['value'];
            }
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
      $products = $this->model::select('products.name', 'products.weight', 
         'products_order.quantity', 'products_order.product_woocommerce_id', 'products_order.cost', 'orders.status', 'orders.shipping_method')
         ->join('products_order', 'products_order.order_id', '=', 'orders.order_woocommerce_id')
         ->join('products', 'products.product_woocommerce_id', '=', 'products_order.product_woocommerce_id')
         ->where('orders.order_woocommerce_id', $order_id)
         ->get();

      // Merge les quantity si plusieurs fois le même produit
      $products = json_decode(json_encode($products), true);
      $array_product_id = [];
      $new_array_product = [];

      foreach($products as $product){
        if(!in_array($product['product_woocommerce_id'], $array_product_id)){
            $array_product_id[] = $product['product_woocommerce_id'];
            $new_array_product[] = $product;
        } else {
            $ids = array_column($new_array_product, "product_woocommerce_id");
            $clesRecherchees = array_keys($ids,  $product['product_woocommerce_id']);
            if(count($clesRecherchees) > 0){
               $new_array_product[$clesRecherchees[0]]['quantity'] = intval($new_array_product[$clesRecherchees[0]]['quantity']) + intval($product['quantity']);
            }
        }
      }

      return $new_array_product;
   }

   public function unassignOrders(){
      try{
         $this->model
            ->whereIn('orders.status', ['processing', 'order-new-distrib', 'en-attente-de-pai'])
            ->update(['user_id' => 0]);

         echo json_encode(['success' => true]);
      } catch(Exception $e){
         echo json_encode(['success' => false, 'message' => $e->getMessage()]);
      }
   }

   public function getOrdersWithoutLabels(){
      $date = date("Y-m-d H:i:s", strtotime("-2 month"));

      return $this->model::select('orders.order_woocommerce_id', 'orders.date')
      ->leftJoin('labels', 'orders.order_woocommerce_id', 'labels.order_id')
      ->leftJoin('distributors', 'distributors.customer_id', 'orders.customer_id')
      ->where([
         ['labels.label', NULL],
         ['orders.status', 'finished'],
         ['orders.shipping_method', '!=', 'local_pickup'],
         ['distributors.role', NULL],
         ['orders.date', '>', $date],
     ])
      ->orderBy('orders.updated_at', 'DESC')
      ->get();


   }

   public function update($data, $order_id){
      try{
         $this->model->where('order_woocommerce_id', $order_id)->update($data);
         echo json_encode(['success' => true]);
      } catch(Exception $e){
         echo json_encode(['success' => false, 'message' => $e->getMessage()]);
      }
   }

   public function getChronoLabelByDate($date){
      return $this->model::select('orders.*', 'label_product_order.*', 'labels.tracking_number', 'labels.created_at as label_created_at', 
      DB::raw("SUM(prepa_products.weight * prepa_label_product_order.quantity) as weight"))
      ->leftJoin('label_product_order', 'label_product_order.order_id', '=', 'orders.order_woocommerce_id')
      ->leftJoin('labels', 'labels.id', '=', 'label_product_order.label_id')
      ->leftJoin('products', 'products.product_woocommerce_id', '=', 'label_product_order.product_id')
      ->where('labels.origin', 'chronopost')
      ->where('labels.created_at', 'LIKE', '%'.$date.'%')
      ->where('labels.bordereau_id', null)
      ->groupBy('orders.id')
      ->get();
   }

   public function delete($order_id){
      try {
         DB::beginTransaction();
         $this->model::where('order_woocommerce_id', $order_id)->delete();
         DB::table('products_order')->where('order_id', $order_id)->delete();

         // Validation et commit de la transaction
         DB::commit();

         return true;
      } catch (\Exception $e) {
         // En cas d'erreur, rollback de la transaction
         DB::rollback();
         return false;
     }
   }


   public function insertOrderAndProducts($data_order, $data_product){
      try {
         DB::beginTransaction();
         $this->model::insert($data_order);
         DB::table('products_order')->insert($data_product);

         // Validation et commit de la transaction
         DB::commit();

         return true;
      } catch (\Exception $e) {
         // En cas d'erreur, rollback de la transaction
         DB::rollback();
         return false;
     }
   }
}























