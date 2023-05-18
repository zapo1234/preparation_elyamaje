<?php

namespace App\Repository\Order;

use Hash;
use Exception;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderRepository implements OrderInterface

{

   private $model;

   public function __construct(Order $model){

      $this->model = $model;
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
                  'customer_first_name' => $orderData['billing']['first_name'],
                  'customer_last_name' => $orderData['billing']['last_name'],
                  'date' => $orderData['date_created'],
                  'total' => $orderData['total'],
                  'user_id' => $userId,
                  'status' => $orderData['status']
               ];
               

               foreach($orderData['line_items'] as $value){
                  $productsToInsert[] = [
                     'order_id' => $orderData['id'],
                     'product_woocommerce_id' => $value['id'],
                     'name' => $value['name'],
                     'quantity' => $value['quantity'],
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
      return $this->model->all();
   }

   public function getOrdersByIdUser($id){



      $list = [];
      $list2 = [];

      $orders = 
      $this->model->join('products', 'products.order_id', '=', 'orders.order_woocommerce_id')
         ->where('user_id', $id)
         ->select('*')->get();

      $orders = json_decode(json_encode($orders), true);

      foreach($orders as $key => $order){
         $list[$order['order_woocommerce_id']]['details'] = [
            'id' => $order['order_woocommerce_id'],
            'first_name' => $order['customer_first_name'],
            'last_name' => $order['customer_last_name'],
            'date' => $order['date'],
            'total' => $order['total'],
            'status' => $order['status'],
         ];
         $list[$order['order_woocommerce_id']]['items'][] = $order;
      }

      $list = array_values($list);

      // dd($list);
      return $list;


   }

}























