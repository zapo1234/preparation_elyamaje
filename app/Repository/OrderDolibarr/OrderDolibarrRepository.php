<?php

namespace App\Repository\OrderDolibarr;

use Exception;
use Throwable;
use App\Models\OrderDolibarr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Http\Service\PDF\InvoicesPdf;


class OrderDolibarrRepository implements OrderDolibarrInterface
{

   private $model;
    private $pdf;

   public function __construct(OrderDolibarr $model,
   InvoicesPdf $pdf){
      $this->model = $model;
      $this->pdf = $pdf;
   }

   public function getAllOrders(){
      $array_order = [];
      $orders = $this->model::select('products.*', 'products.name as productName', 'orders_doli.*', 'orders_doli.id as orderDoliId', 'orders_doli.name as firstname', 'orders_doli.pname as lastname',
      'lines_commande_doli.qte as quantity', 'lines_commande_doli.price as priceDolibarr', 'lines_commande_doli.total_ht', 'lines_commande_doli.total_ttc', 'lines_commande_doli.id as line_items_id_dolibarr',
      'lines_commande_doli.total_tva', 'lines_commande_doli.remise_percent', 'lines_commande_doli.id_product as product_dolibarr_id', 'users.name as preparateur')
         ->Leftjoin('lines_commande_doli', 'lines_commande_doli.id_commande', '=', 'orders_doli.id')
         ->Leftjoin('products', 'products.barcode', '=', 'lines_commande_doli.barcode')
         ->Leftjoin('users', 'users.id', '=', 'orders_doli.user_id')
         ->whereNotIn('orders_doli.statut', ['pending', 'finished', 'canceled'])
         ->get();

      $orders = json_decode(json_encode($orders), true);

      foreach($orders as $order){
         $array_order[$order['id']][] =  $order;
      }

      return $array_order;
   }


   // Pour l'emballage d'une commande
   public function getOrdersDolibarrById($order_id){
      $order_lines = $this->model::select('products.*', 'products.name as productName', 'orders_doli.*', 'orders_doli.id as orderDoliId', 'orders_doli.name as firstname', 'orders_doli.pname as lastname',
      'lines_commande_doli.qte as quantity', 'lines_commande_doli.pick', 'lines_commande_doli.price as priceDolibarr', 'lines_commande_doli.total_ht', 'lines_commande_doli.total_ttc', 'lines_commande_doli.id as line_items_id_dolibarr',
      'lines_commande_doli.total_tva', 'lines_commande_doli.remise_percent', 'lines_commande_doli.id_product as product_dolibarr_id', 'users.name as preparateur')
         ->Leftjoin('lines_commande_doli', 'lines_commande_doli.id_commande', '=', 'orders_doli.id')
         ->Leftjoin('products', 'products.barcode', '=', 'lines_commande_doli.barcode')
         ->Leftjoin('users', 'users.id', '=', 'orders_doli.user_id')
         ->where('products.status', 'publish')
         ->where('orders_doli.ref_order', $order_id)
         ->get();

      foreach($order_lines as $key => $order){
         $order_lines[$key]['name'] = $order['productName'];
         $order_lines[$key]['order_woocommerce_id'] = $order['ref_order'];
         $order_lines[$key]['from_dolibarr'] = true;

         $order_lines[$key]['cost'] = $order['price'];
         $order_lines[$key]['total_order'] = $order['total_order_ttc'];
         $order_lines[$key]['status'] = $order['statut'];
         $order_lines[$key]['shipping_method_detail'] = "Colissimo avec signature";

         $order_lines[$key]['shipping_customer_first_name'] = $order['firstname'];
         $order_lines[$key]['shipping_customer_last_name'] = $order['firstname'] != $order['lastname'] ? $order['lastname'] : '';
         $order_lines[$key]['shipping_customer_company'] = $order['company'];
         $order_lines[$key]['shipping_customer_address_1'] = $order['adresse'];
         $order_lines[$key]['shipping_customer_address_2'] = '';
         $order_lines[$key]['shipping_customer_city'] = $order['city'];
         $order_lines[$key]['shipping_customer_postcode'] = $order['code_postal'];  
         
         $order_lines[$key]['billing_customer_first_name'] = $order['billing_name'] ?? $order_lines[$key]['shipping_customer_first_name'];
         $order_lines[$key]['billing_customer_last_name'] = $order['billing_name'] != $order['billing_pname'] ? $order['billing_pname'] : '' ?? $order_lines[$key]['shipping_customer_last_name'];
         $order_lines[$key]['billing_customer_email'] = $order['email'];
         $order_lines[$key]['billing_customer_address_1'] = $order['billing_adresse'] ?? $order_lines[$key]['shipping_customer_address_1'];
         $order_lines[$key]['billing_customer_address_2'] = '';
         $order_lines[$key]['billing_customer_city'] = $order['billing_city'] ?? $order_lines[$key]['shipping_customer_city'];
         $order_lines[$key]['billing_customer_company'] = $order['billing_company'];
         $order_lines[$key]['billing_customer_postcode'] = $order['billing_code_postal'] ?? $order_lines[$key]['shipping_customer_postcode'];
         $order_lines[$key]['billing_customer_phone'] = $order['phone'];

      }

      return $order_lines;
   }

   public function updateOneOrderAttributionDolibarr($order_id, $user_id){
      return $this->model::where('ref_order', $order_id)->update(['user_id' => $user_id]);
   }

   public function updateOrderAttributionDolibarr($from_user, $to_user){
      return $this->model::where('user_id', $from_user)->where('statut', 'processing')->update(['user_id' => $to_user]);
   }

   public function getUsersWithOrderDolibarr(){
      return $this->model->select('users.*')->whereIn('orders_doli.statut', ['processing', 'waiting_to_validate', 'waiting_validate'])->join('users', 'users.id', '=', 'orders_doli.user_id')->groupBy('users.id')->get();
   }

   public function updateOneOrderStatus($status, $order_id){
      return $this->model::where('ref_order', $order_id)->update(['statut' => $status]);
   }

   public function unassignOrdersDolibarr(){
      try{
         $this->model::where('statut', 'processing')->update(['user_id' => 0]);
         return true;
      } catch(Exception $e){
         return false;
      }
   }

   public function getAllOrdersDolibarrByIdUser($user_id){
      $orders = $this->model::select('products.*', 'products.name as productName', 'orders_doli.*', 'orders_doli.id as orderDoliId', 'orders_doli.name as firstname', 'orders_doli.pname as lastname',
      'lines_commande_doli.qte', 'lines_commande_doli.pick', 'lines_commande_doli.price as priceDolibarr', 'lines_commande_doli.total_ht', 'lines_commande_doli.total_ttc', 'lines_commande_doli.id as line_items_id_dolibarr',
      'lines_commande_doli.total_tva', 'lines_commande_doli.remise_percent')
         ->join('lines_commande_doli', 'lines_commande_doli.id_commande', '=', 'orders_doli.id')
         ->join('products', 'products.barcode', '=', 'lines_commande_doli.barcode')
         ->where('products.status', 'publish')
         ->where('orders_doli.user_id', $user_id)
         ->whereIn('orders_doli.statut', ['processing', 'waiting_to_validate', 'waiting_validate'])
         ->get();

         $orders = json_decode(json_encode($orders), true);
         $list = [];

         foreach($orders as $key => $order){
            $list[$order['ref_order']]['details'] = [
               'id' => $order['ref_order'],
               'first_name' => $order['firstname'],
               'last_name' => $order['firstname'] != $order['lastname'] ? $order['lastname'] : '',
               'date' => $order['date'],
               'total' => floatval($order['total_order_ttc']),
               'total_tax' => floatval($order['total_tax']),
               'status' => $order['statut'],
               'coupons' => '',
               'discount' => 0,
               'discount_amount' => $order['remise_percent'],
               'gift_card_amount' => 0,
               'shipping_amount' => 0,
               'shipping_method' => 'lpc_sign',
               'customer_note'   => null,
               'from_dolibarr' => true
            ];

            $list[$order['ref_order']]['items'][] = [
               "variation" => $order['variation'] == 1 ? $order['product_woocommerce_id'] : 0,
               "name" => $order['productName'],
               "barcode" => $order['barcode'],
               "location" => $order['location'],
               "quantity" => $order['qte'],
               'subtotal_tax' => $order['total_tva'],
               'total_tax' => $order['total_tva'],
               'total' => $order['priceDolibarr'] * $order['qte'],
               "cost" => $order['priceDolibarr'],
               "weight" =>  $order['weight'],
               'pick' => $order['pick'],
               'product_woocommerce_id' => $order['product_woocommerce_id'],
            ];
         }

         return ['orders' => $list];
   }

   public function checkIfDoneOrderDolibarr($order_id, $barcode_array, $products_quantity, $partial){
      $list_product_orders = $this->model::select(DB::raw('REPLACE(prepa_products.barcode, " ", "") AS barcode'), 'lines_commande_doli.qte', 
         'lines_commande_doli.id', 'lines_commande_doli.pick')
            ->join('lines_commande_doli', 'lines_commande_doli.id_commande', '=', 'orders_doli.id')
            ->join('products', 'products.barcode', '=', 'lines_commande_doli.barcode')
            ->where('orders_doli.ref_order', $order_id)
            ->get()
            ->toArray();

      $list_product_orders = json_decode(json_encode($list_product_orders), true);

      $total_product = 0;
      foreach($products_quantity as $product){
         $total_product = $total_product + intval($product);
      }

      $list_products = [];
      foreach($list_product_orders as $list){
         // Ne prend pas en compte les produits déjà bippé
         if($list['qte'] != $list['pick']){
            $list_products[] = [
               "barcode" => $list['barcode'],
               "quantity" =>  $list['qte'],
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

         if(count($clesRecherchees) != 0){
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

      if(count($product_pick_in) > 0){
         // Mise à jour de la valeur pick avec la quantité qui a été bippé pour chaque produit
         $cases = collect($product_pick_in)->map(function ($item) {
            return sprintf("WHEN %d THEN '%s'", $item['id'], intval($item['quantity']));
         })->implode(' ');

         $query = "UPDATE prepa_lines_commande_doli SET pick = (CASE id {$cases} END) WHERE id IN (".implode(',',$lits_id).")";
         DB::statement($query);
      }

      if(!$partial){
         if(!$diff_quantity && !$diff_barcode){
            // Modifie le status de la commande en "Commande préparée",
            $update_status_local = $this->updateOneOrderStatus("prepared-order", $order_id);

            // Insert la commande dans histories
            DB::table('histories')->insert([
               'order_id' => $order_id,
               'user_id' => Auth()->user()->id,
               'status' => 'prepared',
               'created_at' => date('Y-m-d H:i:s'),
               'total_product' => $total_product ?? null
            ]);
            return true;
         } else {
            return false;
         }
      } else {
          // Modifie le status de la commande en "en attente de validation"
         try{
            $this->updateOneOrderStatus("waiting_to_validate", $order_id);
            return true;
         } catch(Exception $e){
            return $e->getMessage();
         }
      }
   }

   public function getProductOrder($order_id){
      return $this->model::select('products.name', 'products.weight', 
         'lines_commande_doli.qte as quantity', 'products.product_woocommerce_id', 'lines_commande_doli.price as cost', 'orders_doli.statut as status')
         ->join('lines_commande_doli', 'lines_commande_doli.id_commande', '=', 'orders_doli.id')
         ->Leftjoin('products', 'products.barcode', '=', 'lines_commande_doli.barcode')
         ->where('orders_doli.ref_order', $order_id)
         ->get();
   }

   public function getAllOrdersAndLabelByFilter($filters){
         $query = $this->model::select('orders_doli.*', 'label_product_order.*', 'labels.tracking_number', 'labels.created_at as label_created_at', 'labels.label_format', 
         'labels.cn23', 'labels.download_cn23', 'labels.origin')
         ->Leftjoin('label_product_order', 'label_product_order.order_id', '=', 'orders_doli.ref_order')
         ->Leftjoin('labels', 'labels.id', '=', 'label_product_order.label_id');

        
         $haveFilter = false;
         foreach($filters as $key => $filter){

            switch ($key) {
               case "status":
                  $key = "statut";
                  break;
               case "order_woocommerce_id":
                  $key = "ref_order";
                  break;
           }

            if($filter){
               $haveFilter = true;
               if($key == "created_at"){
                  $query->where("labels.".$key."","LIKE",  "%".$filter."%");
               } else if($key == "origin"){
                  $query->where("labels.".$key , $filter);
               } else {
                  $query->where("orders_doli.".$key."", $filter);
               }
            }
         }
   

         if(!$haveFilter){
            $date = date('Y-m-d');
            $query->where("labels.created_at","LIKE",  "%".$date."%");
         }
         $query->groupBy('labels.tracking_number');
         $query->orderBy('labels.created_at', 'DESC');
         $query->limit(500);
         $results = $query->get();

         foreach($results as $key => $result){

            $results[$key]['id'] = $result->ref_order;
            $results[$key]['order_woocommerce_id'] = $result->ref_order;
            $results[$key]['status'] = $result->statut;
            $results[$key]['shipping_method'] = str_contains($result->ref_order, 'BP') ? 'chrono' : 'lpc_sign';

            // Billing
            $results[$key]['billing_customer_first_name'] = $result->billing_name;
            $results[$key]['billing_customer_last_name'] = $result->billing_pname;
            $results[$key]['billing_customer_company'] = $result->billing_company;
            $results[$key]['billing_customer_address_1'] = $result->billing_adresse;
            $results[$key]['billing_customer_address_2'] = "";
            $results[$key]['billing_customer_city'] = $result->billing_city;
            $results[$key]['billing_customer_state'] = "";
            $results[$key]['billing_customer_postcode'] = $result->billing_code_postal;
            $results[$key]['billing_customer_country'] = $result->billing_country;
            $results[$key]['billing_customer_email'] = $result->email;
            $results[$key]['billing_customer_phone'] = $result->phone;

            // Shipping
            $results[$key]['shipping_customer_first_name'] = $result->name;
            $results[$key]['shipping_customer_last_name'] = $result->pname;
            $results[$key]['shipping_customer_company'] = $result->company;
            $results[$key]['shipping_customer_address_1'] = $result->adresse;
            $results[$key]['shipping_customer_address_2'] = "";
            $results[$key]['shipping_customer_city'] = $result->city;
            $results[$key]['shipping_customer_state'] = "";
            $results[$key]['shipping_customer_postcode'] = $result->code_postal;
            $results[$key]['shipping_customer_country'] = $result->contry;
         }
         
         return $results;
   }

   public function getAllOrdersAndLabel(){
      $date = date('Y-m-d');
      $results = $this->model::select('orders_doli.id as order_woocommerce_id', 'orders_doli.fk_commande', 'orders_doli.statut as status', 'label_product_order.*', 'labels.tracking_number', 'labels.created_at as label_created_at', 'labels.label_format', 
      'labels.cn23', 'labels.download_cn23', 'labels.origin')
      ->Leftjoin('label_product_order', 'label_product_order.order_id', '=', 'orders_doli.id')
      ->Leftjoin('labels', 'labels.id', '=', 'label_product_order.label_id')
      ->where('labels.created_at', 'LIKE', '%'.$date.'%')
      ->orderBy('labels.created_at', 'DESC')
      // ->limit(50)
      ->get();


      foreach($results as $key => $result){

         $results[$key]['id'] = $result->ref_order;
         $results[$key]['order_woocommerce_id'] = $result->ref_order;
         $results[$key]['status'] = $result->statut;
         $results[$key]['shipping_method'] = str_contains($result->ref_order, 'BP') ? 'chrono' : 'lpc_sign';

         // Billing
         $results[$key]['billing_customer_first_name'] = $result->billing_name;
         $results[$key]['billing_customer_last_name'] = $result->billing_pname;
         $results[$key]['billing_customer_company'] = $result->billing_company;
         $results[$key]['billing_customer_address_1'] = $result->billing_adresse;
         $results[$key]['billing_customer_address_2'] = "";
         $results[$key]['billing_customer_city'] = $result->billing_city;
         $results[$key]['billing_customer_state'] = "";
         $results[$key]['billing_customer_postcode'] = $result->billing_code_postal;
         $results[$key]['billing_customer_country'] = $result->billing_country;
         $results[$key]['billing_customer_email'] = $result->email;
         $results[$key]['billing_customer_phone'] = $result->phone;

         // Shipping
         $results[$key]['shipping_customer_first_name'] = $result->name;
         $results[$key]['shipping_customer_last_name'] = $result->pname;
         $results[$key]['shipping_customer_company'] = $result->company;
         $results[$key]['shipping_customer_address_1'] = $result->adresse;
         $results[$key]['shipping_customer_address_2'] = "";
         $results[$key]['shipping_customer_city'] = $result->city;
         $results[$key]['shipping_customer_state'] = "";
         $results[$key]['shipping_customer_postcode'] = $result->code_postal;
         $results[$key]['shipping_customer_country'] = $result->contry;
      }

      return $results;
   }

   public function orderResetDolibarr($order_id){
      try{
         $update_products = DB::table('lines_commande_doli')->where('id_commande', $order_id)->update(['pick' => 0]);
         return true;
      } catch(Exception $e){
         return false;
      }
   }

   public function getAllProductsPickedDolibarr(){
      $productsPicked = DB::table('lines_commande_doli')
      ->select('products.product_woocommerce_id', 'id_commande as order_id', 'pick')
      ->join('orders_doli', 'orders_doli.id', '=', 'lines_commande_doli.id_commande')
      ->join('products', 'products.barcode', '=', 'lines_commande_doli.barcode')
      ->where('pick', '>',  0)
      ->get()
      ->toArray();

      $productsPicked = json_decode(json_encode($productsPicked), true);

      return $productsPicked;
   }

   public function  updateProductOrder($order_id, $product_id, $data){
      try{
         $update_products = DB::table('lines_commande_doli')->where('id_commande', $order_id)->where('id_product', $product_id)->update($data);
         return true;
      } catch(Exception $e){
         return false;
      }
   }

   public function  deleteProductOrder($order_id, $product_id){
      try{
         $delete_products = DB::table('lines_commande_doli')->where('id_commande', $order_id)->where('id_product', $product_id)->delete();
         return true;
      } catch(Exception $e){
         return false;
      }
   }

   public function updateCustomerDetail($data, $order_id){

      // Converto to field of order_doli
      $array_conversion_fields = [
         'billing_customer_first_name' => 'billing_name',
         'billing_customer_last_name' => 'billing_pname',
         'billing_customer_company' => 'billing_company',
         'billing_customer_address_1' => 'billing_adresse',
         'billing_customer_address_2' => 'billing_adresse',
         'billing_customer_city' => 'billing_city',
         'billing_customer_postcode' => 'billing_code_postal',
         'billing_customer_country' => 'billing_country',
         'billing_customer_email' => 'email',
         'billing_customer_phone' => 'phone',
         'shipping_customer_first_name' => 'name',
         'shipping_customer_last_name' => 'pname',
         'shipping_customer_company' => 'company',
         'shipping_customer_address_1' => 'adresse',
         'shipping_customer_address_2' => 'adresse',
         'shipping_customer_city' => 'city',
         'shipping_customer_postcode' => 'code_postal',
         'shipping_customer_country' => 'contry',
      ];

      $new_array = [];
      foreach($data as $key => $dt){
         $new_array[$array_conversion_fields[$key]] = $dt;
      }


      try{
         $this->model->where('ref_order', $order_id)->update($new_array);
         echo json_encode(['success' => true]);
      } catch(Exception $e){
         echo json_encode(['success' => false, 'message' => $e->getMessage()]);
      }
   }

   public function getOrdersBeautyProf($date){
      return $this->model::select('users.id','users.name', 'orders_doli.statut as status', 'orders_doli.ref_order', 
      'orders_doli.date as created_at',  'orders_doli.id as order_id', 'orders_doli.total_order_ttc')
      ->leftJoin('users', 'users.id', '=', 'orders_doli.seller')
      ->where('orders_doli.date', 'LIKE', '%'.$date.'%')
      ->where('orders_doli.ref_order', 'LIKE', '%BP%')
      ->get()
      ->toArray();
   }


   public function getAllOrdersBeautyProf($user_id = false, $filters = null){

      if($user_id){
         $query = $this->model::select('users.name', 'orders_doli.statut as status', 'orders_doli.ref_order', 
            'orders_doli.date as created_at',  'orders_doli.id as order_id', 'orders_doli.total_order_ttc',
            'orders_doli.name', 'orders_doli.pname')
            ->leftJoin('users', 'users.id', '=', 'orders_doli.seller');

            if($filters){
               foreach($filters as $key => $filter){
                  if($filter){
                     if($key == "created_at"){
                        $query->where("orders_doli.date" ,"LIKE",  "%".$filter."%");
                     } else if($key == "ref_order"){
                        $query->where("orders_doli.".$key."", $filter);
                     }
                  }
               }
            }
            

            $query->where('orders_doli.ref_order', 'LIKE', '%BP%');
            $query->where('orders_doli.seller', $user_id);
            $result = $query->get();
            return $result->toArray();
      } else {
         return $this->model::select('users.id','users.name', 'orders_doli.statut as status', 'orders_doli.ref_order', 
         'orders_doli.date as created_at',  'orders_doli.id as order_id', 'orders_doli.total_order_ttc')
            ->leftJoin('users', 'users.id', '=', 'orders_doli.seller')
            ->where('orders_doli.ref_order', 'LIKE', '%BP%')
            ->get()
            ->toArray();
      }
   }

   public function getAllOrdersBeautyProfHistory($filters = null){
     
      $query = $this->model::select('orders_doli.id as order_id', 'users.name as seller', 'orders_doli.statut as status', 'orders_doli.ref_order', 
         'orders_doli.date as created_at', 'orders_doli.billing_name', 'orders_doli.billing_pname', 'orders_doli.name', 'orders_doli.pname')
         ->leftJoin('users', 'users.id', '=', 'orders_doli.seller');

         if($filters){
            foreach($filters as $key => $filter){
               if($filter){
                  if($key == "created_at"){
                     $query->where("orders_doli.date" ,"LIKE",  "%".$filter."%");
                  } else if($key == "ref_order"){
                     $query->where("orders_doli.".$key."", $filter);
                  }
               }
            }
         }
         
      $query->where('orders_doli.ref_order', 'LIKE', '%BP%');
      $query->whereNotIn('orders_doli.statut', ['pending', 'processing']);
      $result = $query->get();
      return $result->toArray();
     
   }

   public function getAllOrdersPendingBeautyProf($ref_order, $date){

      $date = $date ?? date('Y-m-d');

      if($ref_order){
         return $this->model::select('orders_doli.id as order_id', 'users.name as seller', 'orders_doli.statut as status', 'orders_doli.ref_order', 
            'orders_doli.date as created_at', 'orders_doli.billing_name as name', 'orders_doli.billing_pname as pname')
            ->leftJoin('users', 'users.id', '=', 'orders_doli.seller')
            ->where('orders_doli.statut', 'pending')
            ->where('orders_doli.ref_order', $ref_order)
            ->get()
            ->toArray();
      } else {
         return $this->model::select('orders_doli.id as order_id', 'users.name as seller', 'orders_doli.statut as status', 'orders_doli.ref_order', 
         'orders_doli.date as created_at', 'orders_doli.billing_name as name', 'orders_doli.billing_pname as pname')
            ->leftJoin('users', 'users.id', '=', 'orders_doli.seller')
            ->where('orders_doli.statut', 'pending')
            ->where('orders_doli.date', 'LIKE', '%'.$date.'%')
            ->get()
            ->toArray();
      }
   }

   public function getAllHistory(){
      $list_orders = [];

      $orders = $this->model->join('lines_commande_doli', 'lines_commande_doli.id_commande', '=', 'orders_doli.id')
         ->Leftjoin('products', 'products.barcode', '=', 'lines_commande_doli.barcode')
         ->join('users', 'users.id', '=', 'orders_doli.user_id')
         ->whereIn('orders_doli.statut', ['prepared-order'])
         ->select('orders_doli.*', 'users.name as preparateur','products.product_woocommerce_id', 'products.category', 'products.category_id', 'products.variation',
         'products.name', 'products.barcode', 'products.location', 'lines_commande_doli.pick', 'lines_commande_doli.qte as quantity',
         'lines_commande_doli.total_tva as total_tax','lines_commande_doli.total_ttc as total_price', 'lines_commande_doli.price as cost', 'products.weight', 'lines_commande_doli.remise_percent')
         ->orderBy('products.menu_order', 'ASC')
         ->get();

      $orders = json_decode(json_encode($orders), true);

      // Cas de produits double si par exemple 1 en cadeau et 1 normal
      $product_double = [];
      foreach($orders as $key => $list){
        
         if(isset($product_double[$list['ref_order']][$list["barcode"]])){
            $quantity = $product_double[$list['ref_order']][$list["barcode"]]['quantity'];
            $key_barcode_to_remove = $product_double[$list['ref_order']][$list["barcode"]]['key_barcode_to_remove'];
   
            unset($orders[$key_barcode_to_remove]);
            $orders[$key]['quantity'] = $orders[$key]['quantity'] + $quantity;
            $orders[$key]['pick'] = $orders[$key]['quantity'];
         } else {
            $product_double[$list['ref_order']][$list["barcode"]] = [
               'quantity' => $list['quantity'],
               'key_barcode_to_remove' => $key
            ];
             
         }
      }

      // Reconstruis le tableaux sans trou dans les clés à cause du unset précédent
      foreach($orders as $order){
         $list_orders[$order['ref_order']]['preparateur'] =  $order['preparateur'];
         $list_orders[$order['ref_order']]['details'] = [
            'id' => $order['ref_order'],
            'first_name' => $order['billing_name'],
            'last_name' => $order['billing_pname'],
            'date' => $order['date'],
            'total' => $order['total_order_ttc'],
            'total_tax' => $order['total_tax'],
            'status' => $order['statut'],
            'coupons' => '',
            'discount' => '',
            'discount_amount' => 0,
            'gift_card_amount' => 0,
            'shipping_amount' => 0,
         ];
         $list_orders[$order['ref_order']]['items'][] = $order;
      }

      $list_orders = array_values($list_orders);
      return $list_orders;
   }

   public function getOrderDetails($order_id){
      // récuperation du detail de commande.
      $details_order = $this->model->select('orders_doli.statut', 'lines_commande_doli.id','id_commande','id_product','libelle','lines_commande_doli.price','qte','lines_commande_doli.remise_percent','lines_commande_doli.total_ht')
      ->join('lines_commande_doli', 'orders_doli.id', '=', 'lines_commande_doli.id_commande')
      ->where('orders_doli.ref_order', '=', $order_id)
      ->get();
 
      $list_tiers_order = json_encode($details_order);
      $list_tiers_order = json_decode($details_order,true);
      $data_list_details =[];

      foreach($list_tiers_order as $values){
            $data_list_details[] =[
            'id'=>$values['id'],
            'id_commande'=>$values['id_commande'],
            'id_product'=>$values['id_product'],
            'nom'=>$values['libelle'],
            'quantite'=>$values['qte'],
            'prix'=>$values['price'],
            'status' =>$values['statut']
         ];

      }

      // afficher ici.
      return $data_list_details;
   }

   public function  updateStock($data, $typeUpdate){
      try {

         if ($typeUpdate == "decrementation") {
            $variable = "THEN warehouse_array_list -";
         }else {
            $variable = "THEN warehouse_array_list +";
         }

         DB::beginTransaction();

         $updates = [];
         foreach ($data as $item) {
             $updates[$item['id_product']] = $item['quantite'];
         }

         $caseStatements = '';
         foreach ($updates as $id_product => $decrementValue) {
            $caseStatements .= "WHEN $id_product $variable $decrementValue ";
         }

         $res = DB::update("
            UPDATE prepa_products_dolibarr
            SET warehouse_array_list = CASE product_id
               $caseStatements
               ELSE warehouse_array_list
            END
            WHERE product_id IN (" . implode(',', array_keys($updates)) . ")
         ");

         
         if (count($data) == $res) {
            DB::commit();
            return true;
         }else {
            DB::rollBack();
            return false;
         }

      } catch (Throwable $th) {
         return false;;
      } 
   }

   public function getChronoLabelByDate($date){
      $labels = [];
      $labels_dolibarr = $this->model::select('orders_doli.*', 'label_product_order.*', 'labels.tracking_number', 'labels.created_at as label_created_at', 
      DB::raw("SUM(prepa_products.weight * prepa_label_product_order.quantity) as weight"))
      ->leftJoin('label_product_order', 'label_product_order.order_id', '=', 'orders_doli.ref_order')
      ->leftJoin('labels', 'labels.id', '=', 'label_product_order.label_id')
      ->leftJoin('products', 'products.product_woocommerce_id', '=', 'label_product_order.product_id')
      ->where('labels.origin', 'chronopost')
      ->where('labels.created_at', 'LIKE', '%'.$date.'%')
      ->where('labels.bordereau_id', null)
      ->groupBy('orders_doli.ref_order')
      ->get();

      foreach($labels_dolibarr as $label){
         $labels[] = [
            'order_woocommerce_id' => $label['ref_order'],
            'weight' => $label['weight'],
            'tracking_number' => $label['tracking_number'],
            'shipping_method' => $label['shipping_method'],
            'product_code' => null,
            'shipping_customer_company' => $label['billing_company'],
            'shipping_customer_last_name' => $label['billing_pname'],
            'shipping_customer_first_name' => $label['billing_name'],
            'shipping_customer_postcode' => $label['code_postal'],
            'shipping_customer_city' => $label['city'],
            'shipping_customer_country' => $label['contry'],
            'customer_id' => $label['socid'] ?? null,
            'total_order' => $label['total_order_ttc']
         ];
      }
      return $labels; 
   }


     public function getOrderidfact($ref_commande,$indexs){
         // recupérer id de la commande...
         
         $userdata =  DB::table('orders_doli')->select('id','ref_order')->where('ref_order','=',$ref_commande)->get();
         $ids = json_encode($userdata);
         $id_recup = json_decode($ids,true);
        
         if(count($id_recup)!=0){
            $id_commande = $id_recup[0]['id'];// recupérer id de commmande.
            $usersWithPosts = DB::table('orders_doli')
            ->join('lines_commande_doli', 'orders_doli.id', '=', 'lines_commande_doli.id_commande')
             ->select('lines_commande_doli.*', 'orders_doli.ref_order','orders_doli.name','orders_doli.pname','orders_doli.adresse','orders_doli.code_postal','orders_doli.email',
            'orders_doli.total_tax','orders_doli.total_order_ttc','orders_doli.ref_order','orders_doli.city','orders_doli.phone','orders_doli.billing_adresse','orders_doli.billing_city','orders_doli.billing_code_postal',
            'orders_doli.billing_code_postal','orders_doli.billing_pname','orders_doli.billing_name')
            ->where('orders_doli.id','=',$id_commande)
            ->get();
      
            $lists = json_encode($usersWithPosts);
            $result = json_decode($lists,true);

            // traiter le retour de la facture
           // verifions l'existence des resultats.
            if(count($result)!=0){
              // recupérer les variables utile pour envoyé la facture et l'email au clients.
            $tiers =[
           'ref_order'=>$result[0]['ref_order'],
           'name' => $result[0]['billing_name'],
           'pname' => $result[0]['billing_pname'],
           'adresse' => $result[0]['billing_adresse'],
           'code_postal' => $result[0]['billing_code_postal'],
           'city'=> $result[0]['billing_city'],
            'contry' => 'FR',
           'email' =>$result[0]['email'],
           'phone' => $result[0]['phone'],
           
           ];
     
             // construire le tableau des produit liée dans la commande.
           foreach($result as $val){
                $data_line_order[] = [
                'id_commande'=> $val['id_commande'],
                 'libelle' => $val['libelle'],
                'price'=> $val['price'],
                'qte'=> $val['qte'],
                'total_ht'=> number_format($val['total_ht'], 2),
                'total_ttc'=> number_format($val['total_ttc'], 2),
                 'remise' => 30,
                'prix_remise'=> number_format($val['total_ttc'], 2)*0.7,
              ' total_tva'=> number_format($val['total_ttc'] - $val['total_ht'], 2),
           ];
        }
 
           // le destinatire et la date d'aujourdhuit.
           $destinataire = $result[0]['email'];
           $total_ttc = $result[0]['total_order_ttc'];
           // definir le pourcentage du code promo envoyé  au tiers
        
           if($total_ttc >= 80){
              $percent=10;
           }
            if($total_ttc < 80){
               $percent=5;
           }
           $total_ht = number_format($total_ttc * 0.8, 2);
         // ref de la commande
          $ref_order = $result[0]['ref_order'];
           // recupérer les 4 premiers lettre du nom de la cliente...
           $name_code = substr($result[0]['pname'], 0, 4);
         // Mettre en majuscule le resultat.
          $name_prefix_code = strtoupper($name_code);
          // génére un code promo a donner au clients.
          $code_promos ="BPP-$id_commande$name_prefix_code-2024";

          $res_name = str_replace( array( '%', '@', '\'', ';', '<', '>' ), ' ', $code_promos);// filtre sur les caractère spéciaux
         //$code_promo = preg_replace("/\s+/", "", $res_name);// suprime les espace dans la chaine.
         $code_promo = $res_name;

         $remise_true = env('DISCOUNT');
         $remise = $remise_true*100;
         
          // declencher la génération de facture et envoi de mail.
         $this->pdf->invoicespdf($data_line_order,$tiers, $ref_order, $total_ht, $total_ttc, $destinataire,$code_promo,$remise,$percent,$indexs);
         // insert dans la base de données...
          $datas_promo =[
         'id_commande'=>$id_commande,
         'code_promo'=>$code_promo,
         'percent'=>$percent,
         'email'=>$result[0]['email'],
         'created_at'=> date('Y-m-d H:i:s'),
         'updated_at'=> date('Y-m-d H:i:s'),
         ];
         // insert les données dans la base de données.
         //  DB::table('code_promos')->insert($datas_promo);
          return $ref_order;
      }
      else{
             // afficher une erreur ....
             // insert dans la table des erreur log.
             $message =" Attention la commande Beauty proof paris $id_commande ne contient pas de produits !";
             $datas = [
               'order_id'=> $id_commande,
               'message'=> $message,
               'created_at'=>date('Y-m-d h:i:s'),
               'created_at'=>date('Y-m-d h:i:s'),
               'updated_at'=>date('Y-m-d h:i:s')
              ];

               // insert dans la table 
               $this->geterrorcommande($datas);
     
              echo json_encode(['success' => false, 'message' => $message]);
         }

          }

         else{

              dd('commande introuvable');
         }

  

     }

     public function getAllReforder(){
       $order_all =[];
       $userdata =  DB::table('orders_doli')->select('ref_order')->get();
       $ids = json_encode($userdata);
       $list_ids = json_decode($ids,true);

       foreach($list_ids as $key => $val){
           $order_all[$key] =$val['ref_order'];
       }

       return $order_all;
     }

     public function  getTiersBp(){


     }

     public function  getOrderBp(){

      
     }
}























