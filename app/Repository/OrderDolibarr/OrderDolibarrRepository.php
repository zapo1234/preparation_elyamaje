<?php

namespace App\Repository\OrderDolibarr;

use Exception;
use App\Models\OrderDolibarr;
use Illuminate\Support\Facades\DB;


class OrderDolibarrRepository implements OrderDolibarrInterface
{

   private $model;

   public function __construct(OrderDolibarr $model){
      $this->model = $model;
   }

   public function getAllOrders(){
      $array_order = [];
      $orders = $this->model::select('products.*', 'products.name as productName', 'orders_doli.*', 'orders_doli.id as orderDoliId', 'orders_doli.name as firstname', 'orders_doli.pname as lastname',
      'lines_commande_doli.qte as quantity', 'lines_commande_doli.price as priceDolibarr', 'lines_commande_doli.total_ht', 'lines_commande_doli.total_ttc', 'lines_commande_doli.id as line_items_id_dolibarr',
      'lines_commande_doli.total_tva', 'lines_commande_doli.remise_percent', 'lines_commande_doli.id_product as product_dolibarr_id', 'users.name as preparateur')
         ->join('lines_commande_doli', 'lines_commande_doli.id_commande', '=', 'orders_doli.id')
         ->join('products', 'products.barcode', '=', 'lines_commande_doli.barcode')
         ->Leftjoin('users', 'users.id', '=', 'orders_doli.user_id')
         ->where('orders_doli.statut', '!=', 'finished')
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

         $order_lines[$key]['billing_customer_first_name'] = $order['firstname'];
         $order_lines[$key]['billing_customer_last_name'] = $order['firstname'] != $order['lastname'] ? $order['lastname'] : '';
         $order_lines[$key]['billing_customer_email'] = $order['email'];
         $order_lines[$key]['billing_customer_address_1'] = $order['adresse'];
         $order_lines[$key]['billing_customer_address_2'] = '';

         $order_lines[$key]['shipping_customer_first_name'] = $order['firstname'];
         $order_lines[$key]['shipping_customer_last_name'] = $order['firstname'] != $order['lastname'] ? $order['lastname'] : '';
         $order_lines[$key]['shipping_customer_company'] = $order['company'];
         $order_lines[$key]['shipping_customer_address_1'] = $order['adresse'];
         $order_lines[$key]['shipping_customer_address_2'] = '';
         $order_lines[$key]['shipping_customer_city'] = $order['city'];
         $order_lines[$key]['shipping_customer_postcode'] = $order['code_postal'];    
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
         ->join('products', 'products.barcode', '=', 'lines_commande_doli.barcode')
         ->where('orders_doli.ref_order', $order_id)
         ->get();
   }

   public function getAllOrdersAndLabelByFilter($filters){
         $query = $this->model::select('orders_doli.ref_order as order_woocommerce_id', 'orders_doli.fk_commande', 'orders_doli.statut as status', 'label_product_order.*', 'labels.tracking_number', 'labels.created_at as label_created_at', 'labels.label_format', 
         'labels.cn23', 'labels.download_cn23')
         ->Leftjoin('label_product_order', 'label_product_order.order_id', '=', 'orders_doli.id')
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
         return $results;
   }

   public function getAllOrdersAndLabel(){
      $date = date('Y-m-d');
      return $this->model::select('orders_doli.id as order_woocommerce_id', 'orders_doli.fk_commande', 'orders_doli.statut as status', 'label_product_order.*', 'labels.tracking_number', 'labels.created_at as label_created_at', 'labels.label_format', 
      'labels.cn23', 'labels.download_cn23')
      ->Leftjoin('label_product_order', 'label_product_order.order_id', '=', 'orders_doli.id')
      ->Leftjoin('labels', 'labels.id', '=', 'label_product_order.label_id')
      ->where('labels.created_at', 'LIKE', '%'.$date.'%')
      ->orderBy('labels.created_at', 'DESC')
      // ->limit(50)
      ->get();
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
}























