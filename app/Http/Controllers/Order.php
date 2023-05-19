<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
use App\Repository\Order\OrderRepository;
use App\Repository\User\UserRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Order extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private $api;
    private $user;
    private $order;


    public function __construct(Api $api, UserRepository $user, OrderRepository $order){
      $this->api = $api;
      $this->user = $user;
      $this->order = $order;
    }

    public function orders($id = null){

      if($id){
        $orders_user = $this->order->getOrdersByIdUser($id);
        return $orders_user;
      } else {
        $status = "processing"; // Commande en préparation
        $per_page = 100;
        $page = 1;
        $orders = $this->api->getOrdersWoocommerce($status, $per_page, $page);
        $count = count($orders);
  
        // Check if others page
        if($count == 100){
          while($count == 100){
            $page = $page + 1;
            $orders_other = $this->api->getOrdersWoocommerce($status, $per_page, $page);
           
            if(count($orders_other ) > 0){
              $orders = array_merge($orders, $orders_other);
            }
          
            $count = count($orders_other);
          }
        }
  
        return $orders;
      }
      
    }
 
    public function getOrder(){
      if(Auth()->user()->role == 1){
        echo json_encode($this->orders());
      } else {
        return $this->orders(Auth()->user()->id);
      }
    }


    // Répartis les commandes woocommerce
    public function distributionOrders(){

      // Liste des utilisateurs avec le rôle préparateur
      $users =  $this->user->getUsersByRole(2);
      $array_user = [];
      $orders_user = [];
      $orders_id = [];
      $orders_to_delete = [];

      foreach($users as $user){
        $array_user[$user->id] = [];
      }

      // Liste des commandes déjà réparties entres les utilisateurs
      $orders_user = $this->order->getOrdersByUsers()->toArray();


      foreach($orders_user as $order){
        $array_user[$order['user_id']][] =  $order;
        $orders_id [] = $order['order_woocommerce_id'];
      }


      // Liste des commandes Woocommerce
      $orders = $this->orders();

      $ids = array_column($orders, "id");
      foreach($orders_id as $id){
        $clesRecherchees = array_keys($ids,  $id);
        if(count($clesRecherchees) == 0){
          $orders_to_delete [] = $id;
        }
      }

      // Supprime du tableau les commandes à ne pas prendre en compte
      foreach($array_user as $key => $array){
        foreach($array as $key2 => $arr){
          if(in_array($arr['order_woocommerce_id'], $orders_to_delete)){
              unset($array_user[$key][$key2]);
          }
        }
      }

      // Modifie le status des commandes qui ne sont plus en cours dans woocommerce
      $this->order->updateOrdersById($orders_to_delete);

      // Répartitions des commandes
      foreach($orders as $order){  
      
        foreach($array_user as $key => $array){

          // Check si commande pas déjà répartie
          if(!in_array($order['id'], $orders_id)){
            $tailles = array_map('count', $array_user);
            $cléMin = array_search(min($tailles), $tailles);
  
            if($key == $cléMin){
              array_push($array_user[$key], $order);
              break;
            }
          }
         
        }
      }

      // List orders by users
      $this->order->insertOrdersByUsers($array_user);
      
    }
}




