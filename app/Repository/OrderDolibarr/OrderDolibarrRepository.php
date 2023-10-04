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
      'lines_commande_doli.total_tva', 'lines_commande_doli.remise_percent', 'users.name as preparateur')
         ->join('lines_commande_doli', 'lines_commande_doli.id_commande', '=', 'orders_doli.id')
         ->join('products', 'products.barcode', '=', 'lines_commande_doli.barcode')
         ->join('users', 'users.id', '=', 'orders_doli.user_id')
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
      'lines_commande_doli.qte as quantity', 'lines_commande_doli.price as priceDolibarr', 'lines_commande_doli.total_ht', 'lines_commande_doli.total_ttc', 'lines_commande_doli.id as line_items_id_dolibarr',
      'lines_commande_doli.total_tva', 'lines_commande_doli.remise_percent', 'users.name as preparateur')
         ->join('lines_commande_doli', 'lines_commande_doli.id_commande', '=', 'orders_doli.id')
         ->join('products', 'products.barcode', '=', 'lines_commande_doli.barcode')
         ->join('users', 'users.id', '=', 'orders_doli.user_id')
         ->where('orders_doli.id', $order_id)
         ->get();

      foreach($order_lines as $key => $order){
         $order_lines[$key]['name'] = $order['productName'];
         $order_lines[$key]['order_woocommerce_id'] = $order['id'];
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
      return $this->model::where('id', $order_id)->update(['user_id' => $user_id]);
   }

   public function updateOneOrderStatus($status, $order_id){
      return $this->model::where('id', $order_id)->update(['statut' => $status]);
   }

   public function unassignOrdersDolibarr(){
      return $this->model->update(['user_id' => 0]);
   }

   public function getAllOrdersDolibarrByIdUser($user_id){
      $orders = $this->model::select('products.*', 'products.name as productName', 'orders_doli.*', 'orders_doli.id as orderDoliId', 'orders_doli.name as firstname', 'orders_doli.pname as lastname',
      'lines_commande_doli.qte', 'lines_commande_doli.price as priceDolibarr', 'lines_commande_doli.total_ht', 'lines_commande_doli.total_ttc', 'lines_commande_doli.id as line_items_id_dolibarr',
      'lines_commande_doli.total_tva', 'lines_commande_doli.remise_percent')
         ->join('lines_commande_doli', 'lines_commande_doli.id_commande', '=', 'orders_doli.id')
         ->join('products', 'products.barcode', '=', 'lines_commande_doli.barcode')
         ->where('orders_doli.user_id', $user_id)
         ->whereIn('orders_doli.statut', ['processing', 'waiting_to_validate', 'waiting_validate'])
         ->get();

         $orders = json_decode(json_encode($orders), true);
         $list = [];

         foreach($orders as $key => $order){
            $list[$order['orderDoliId']]['details'] = [
               'id' => $order['orderDoliId'],
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

            $list[$order['orderDoliId']]['items'][] = [
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
               'pick' => 0,
               'product_woocommerce_id' => $order['product_woocommerce_id'],
            ];
         }

         return ['orders' => $list];
   }

   public function checkIfDoneOrderDolibarr($order_id, $barcode_array, $products_quantity, $partial){
      $list_product_orders = $this->model::select(DB::raw('REPLACE(prepa_products.barcode, " ", "") AS barcode'), 'lines_commande_doli.qte', 'lines_commande_doli.id')
            ->join('lines_commande_doli', 'lines_commande_doli.id_commande', '=', 'orders_doli.id')
            ->join('products', 'products.barcode', '=', 'lines_commande_doli.barcode')
            ->where('orders_doli.id', $order_id)
            ->get()
            ->toArray();

      $list_product_orders = json_decode(json_encode($list_product_orders), true);

      $list_products = [];
      foreach($list_product_orders as $list){
         $list_products[] = [
            "barcode" => $list['barcode'],
            "quantity" =>  $list['qte'],
            "id" =>  $list['id'],
         ];
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

    
      // Mise à jour de la valeur pick avec la quantité qui a été bippé pour chaque produit
      $cases = collect($product_pick_in)->map(function ($item) {
         return sprintf("WHEN %d THEN '%s'", $item['id'], intval($item['quantity']));
      })->implode(' ');

      $query = "UPDATE prepa_lines_commande_doli SET pick = (CASE id {$cases} END) WHERE id IN (".implode(',',$lits_id).")";
      DB::statement($query);

      if(!$partial){
         if(!$diff_quantity && !$diff_barcode){
            // Modifie le status de la commande en "Commande préparée",
            $update_status_local = $this->updateOneOrderStatus("prepared-order", $order_id);

            // Insert la commande dans histories
            DB::table('histories')->insert([
               'order_id' => $order_id,
               'user_id' => Auth()->user()->id,
               'status' => 'prepared',
               'created_at' => date('Y-m-d H:i:s')
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
}























