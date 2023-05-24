<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
use App\Repository\Order\OrderRepository;
use App\Repository\User\UserRepository;
use App\Http\Service\Api\TransferOrder;
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
    private $factorder;


    public function __construct(Api $api, UserRepository $user, 
    OrderRepository $order,
    TransferOrder $factorder){
      $this->api = $api;
      $this->user = $user;
      $this->order = $order;
      $this->factorder =$factorder;
    }

    public function orders($id = null, $distributeur = false){

      if($id){
        $orders_user = $this->order->getOrdersByIdUser($id, $distributeur);
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

        // Récupère les commandes attribuée en base s'il y en a 
        $orders_distributed = $this->order->getOrdersByUsers()->toArray();
        $ids = array_column($orders_distributed, "order_woocommerce_id");

        foreach($orders as $key => $order){
          $clesRecherchees = array_keys($ids,  $order['id']);
          if(count($clesRecherchees) > 0){
            $orders[$key]['user_id'] =  $orders_distributed[$clesRecherchees[0]]['user_id'];
            $orders[$key]['name'] =  $orders_distributed[$clesRecherchees[0]]['name'];
          } else {
            $orders[$key]['user_id'] = null;
            $orders[$key]['name'] = "Non attribuée";
          }

        }

        return $orders;
      }
      
    }
 
    public function getOrder(){
      if(Auth()->user()->role_id == 1 || Auth()->user()->role_id == 4){
        $users =  $this->user->getUsersByRole([2])->toArray();
        echo json_encode(['orders' => $this->orders(), 'users' => $users]);
      } else {
        return $this->orders(Auth()->user()->id);
      }
    }

    public function getOrderDistributeur(){
      return $this->orders(Auth()->user()->id, true);
    }


    // Répartis les commandes woocommerce
    public function distributionOrders(){

      // Liste des utilisateurs avec le rôle préparateur
      $users =  $this->user->getUsersByRole([2]);

      $array_user = [];
      $orders_user = [];
      $orders_id = [];
      $orders_to_delete = [];

      foreach($users as $user){
        $array_user[$user->user_id] = [];
      }

      if(count($array_user) == 0){
        echo json_encode(['success' => false, 'message' => 'Il n\'y a pas de préparateur !']);
        return;
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


    public function ordersPrepared(Request $request){
      $barcode_array = $request->post('pick_items');
      $order_id = $request->post('order_id');

      if($barcode_array && $order_id){
        $check_if_order_done = $this->order->checkIfDone($order_id, $barcode_array);
        echo json_encode(["success" => $check_if_order_done]);
      } else {
        echo json_encode(["success" => false]);
      }
     
    }

    public function ordersReset(Request $request){
      $order_id = $request->post('order_id');
      $orderReset = $this->order->orderReset($order_id);
      echo json_encode(["success" => $orderReset]);
    }


    public function updateAttributionOrder(Request $request){
      $from_user = $request->post('from_user');
      $to_user = $request->post('to_user');

      if($from_user && $to_user){
        echo json_encode(["success" => $this->order->updateOrderAttribution($from_user, $to_user)]);
      } else {
        echo json_encode(["success" => false]);
      }
    }


    public function updateOneOrderAttribution(Request $request){
      $order_id = $request->post('order_id');
      $user_id = $request->post('user_id');

      if($order_id && $user_id){
        $update = $this->order->updateOneOrderAttribution($order_id, $user_id);
        $number_order_attributed = $this->order->getOrdersByUsers();
        echo json_encode(["success" => $update, 'number_order_attributed' => count($number_order_attributed)]);
      } else {
        echo json_encode(["success" => false]);
      }
    }


    public function validWrapOrder(Request $request){


        //$order_id = $request->post('order_id');
        $order_id="64686";
        $order = $this->order->getOrderById($order_id);

        if($order){
            $order_new_array = [];
            $products = [];
            $billing = [];
            $shipping = [];

            // Construis le tableau de la même manière que woocommerce
            foreach($order as $key => $or){
              $products['line_items'][] = ['name' => $or['name'], 'product_id' => $or['product_woocommerce_id'], 'variation_id' => $or['variation_id'], 
              'quantity' => $or['quantity'], 'subtotal' => $or['cost'], 'total' => $or['total_price'],
              'meta_data' => [['key' => 'barcode', "value" => $or['barcode']]]];

              
              if(count($billing) == 0 && count($shipping) == 0){
                foreach($or as $key2 => $or2){
                  if (str_contains($key2, 'billing')) {
                    unset($order[$key][$key2]);
                    $billing[count(explode('_', $key2)) > 3 ? explode('_', $key2)[2].'_'.explode('_', $key2)[3] : explode('_', $key2)[2]] = $or2;
                  }

                  if (str_contains($key2, 'shipping')) {
                    unset($order[$key][$key2]);
                    $shipping[count(explode('_', $key2)) > 3 ? explode('_', $key2)[2].'_'.explode('_', $key2)[3] : explode('_', $key2)[2]] = $or2;
                  }
                }
              }

            
            }

            $order_new_array =  $order[0];
            $order_new_array['line_items'] = $products['line_items'];
            $order_new_array['billing'] = $billing;
            $order_new_array['shipping'] = $shipping;

               // Return all order details
               dd($order_new_array);
               // recupérer les function d'ecriture  et création de client et facture dans dolibar.
               $order = $order_new_array;
              
               $this->factorder->Transferorder($order);
            
        }
    }

}


