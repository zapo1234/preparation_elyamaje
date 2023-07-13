<?php

namespace App\Repository\History;

use App\Models\History;
use Illuminate\Support\Facades\DB;

class HistoryRepository implements HistoryInterface
{
   

   private $model;

   public function __construct(History $model){
      $this->model = $model;
   }

   public function getHistoryByDate($date){
      return $this->model::select('users.id', 'users.name', 'histories.status', 'histories.order_id', 'histories.poste', 'products_order.quantity',
      'products_order.product_woocommerce_id')
         ->join('users', 'users.id', '=', 'histories.user_id')
         ->join('orders', 'orders.order_woocommerce_id', '=', 'histories.order_id')
         ->join('products_order', 'products_order.order_id', '=', 'histories.order_id')
         ->where('histories.created_at', 'LIKE', '%'.$date.'%')
         ->get()
         ->toArray();
   }

   public function getAllHistory(){
      return $this->model::select('histories.id as histo', 'users.id', 'users.name', 'histories.poste', 'histories.created_at', 'histories.order_id',
      'histories.status')
         ->join('users', 'users.id', '=', 'histories.user_id')
         ->groupBy('histories.id')
         ->orderBy('histories.created_at', 'DESC')
         ->get()
         ->toArray();
   }

   // Uniquement utilisé par l'admin
   public function getHistoryAdmin(){
      return $this->model::select('users.id', 'users.name', 'histories.status', 'histories.order_id', 'histories.poste', 
         DB::raw('SUM(prepa_products_order.quantity) as total_quantity'),
         'products_order.product_woocommerce_id', 'histories.created_at')
         ->join('users', 'users.id', '=', 'histories.user_id')
         ->join('orders', 'orders.order_woocommerce_id', '=', 'histories.order_id')
         ->join('products_order', 'products_order.order_id', '=', 'histories.order_id')
         ->groupBy('histories.id')
         ->get()
         ->toArray();
   }

   public function save($data){
      return $this->model::insert($data);
   }
}























