<?php

namespace App\Repository\History;

use App\Models\History;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class HistoryRepository implements HistoryInterface
{
   

   private $model;

   public function __construct(History $model){
      $this->model = $model;
   }

   public function getHistoryByDate($date){
      return $this->model::select('users.id', 'users.name', 'histories.status', 'histories.order_id', 'histories.poste', 'histories.total_product')
         ->join('users', 'users.id', '=', 'histories.user_id')
         ->where('histories.created_at', 'LIKE', '%'.$date.'%')
         ->get()
         ->toArray();
   }

   public function getAllHistory($request){

      $date = $request['created_at'] ?? date('Y-m-d');
      $order_id = $request['order_woocommerce_id'] ?? false;

      if($order_id){
         return  $this->model::select('histories.id as histo', 'users.id', 'users.name', 'histories.poste', 'histories.created_at', 'histories.order_id',
         'histories.status', 'orders.status as order_status', 'orders_doli.statut as order_dolibarr_status', 'hist_reassort.status as order_transfer_status')
            ->Leftjoin('users', 'users.id', '=', 'histories.user_id')
            ->Leftjoin('orders', 'orders.order_woocommerce_id', '=', 'histories.order_id')
            ->Leftjoin('orders_doli', 'orders_doli.ref_order', '=', 'histories.order_id')
            ->Leftjoin('hist_reassort', 'hist_reassort.identifiant_reassort', '=', 'histories.order_id')
            ->where('histories.order_id', $order_id)
            ->groupBy('histories.id')
            ->orderBy('histories.created_at', 'DESC')
            ->get()
            ->toArray();
      } else {
         return $this->model::select('histories.id as histo', 'users.id', 'users.name', 'histories.poste', 'histories.created_at', 'histories.order_id',
         'histories.status', 'orders.status as order_status', 'orders_doli.statut as order_dolibarr_status', 'hist_reassort.status as order_transfer_status')
            ->Leftjoin('users', 'users.id', '=', 'histories.user_id')
            ->Leftjoin('orders', 'orders.order_woocommerce_id', '=', 'histories.order_id')
            ->Leftjoin('orders_doli', 'orders_doli.ref_order', '=', 'histories.order_id')
            ->Leftjoin('hist_reassort', 'hist_reassort.identifiant_reassort', '=', 'histories.order_id')
            ->where('histories.created_at', 'LIKE', '%'.$date.'%')
            ->groupBy('histories.id')
            ->orderBy('histories.created_at', 'DESC')
            ->get()
            ->toArray();
      } 
   }

   // Uniquement utilisé par l'admin
   public function getHistoryAdmin($date){
      return $this->model::select('orders.total_order', 'orders.total_order_ttc', 'users.id', 'users.name', 'histories.status', 'histories.order_id', 'histories.poste', 'total_product',
         // DB::raw('SUM(prepa_products_order.quantity) as total_quantity'), 'products_order.product_woocommerce_id', 
         'histories.created_at')
         ->leftJoin('orders', 'orders.order_woocommerce_id', '=', 'histories.order_id')
         ->leftJoin('orders_doli', 'orders_doli.ref_order', '=', 'histories.order_id')

         ->leftJoin('users', 'users.id', '=', 'histories.user_id')
         ->groupBy('histories.id')
         ->where('histories.created_at', 'LIKE', '%'.$date.'%')
         ->get()
         ->toArray();
   }

   public function getAllHistoryAdmin(){
      $data = Cache::remember('histories', 3600, function () {
         return  $this->model::select('users.id', 'users.name', 'histories.status', 'histories.order_id', 'histories.poste', 'total_product',
            // DB::raw('SUM(prepa_products_order.quantity) as total_quantity'), 'products_order.product_woocommerce_id', 
            'histories.created_at')
            ->leftJoin('users', 'users.id', '=', 'histories.user_id')
            // ->leftJoin('products_order', 'products_order.order_id', '=', 'histories.order_id')
            ->groupBy('histories.id')
            ->get()
            ->toArray();
      });

      return $data;
   }

   public function getHistoryByIdUser($user_id){
      return  $this->model::select('histories.status', 'total_product',
         'histories.created_at')
         ->groupBy('histories.id')
         ->where('user_id', $user_id)
         ->get()
         ->toArray();
   }

   public function save($data){
      return $this->model::insert($data);
   }
}
























