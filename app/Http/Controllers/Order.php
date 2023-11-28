<?php

namespace App\Http\Controllers;

use Exception;
use League\Csv\Reader;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
use App\Events\NotificationPusher;
// use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Http\Service\Api\Colissimo;
use App\Http\Service\PDF\CreatePdf;
use App\Http\Service\Api\TransferOrder;
use App\Repository\User\UserRepository;
use App\Repository\Label\LabelRepository;
use App\Repository\Order\OrderRepository;
use App\Repository\History\HistoryRepository;
use App\Repository\Printer\PrinterRepository;
use App\Repository\Product\ProductRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Http\Service\Api\Chronopost\Chronopost;
use App\Repository\LogError\LogErrorRepository;
use App\Repository\Reassort\ReassortRepository;
use App\Repository\Colissimo\ColissimoRepository;
use App\Http\Service\Woocommerce\WoocommerceService;
use Illuminate\Routing\Controller as BaseController;
use App\Repository\Commandeids\CommandeidsRepository;
use App\Repository\Distributor\DistributorRepository;
use App\Repository\Notification\NotificationRepository;
use App\Repository\ProductOrder\ProductOrderRepository;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Repository\OrderDolibarr\OrderDolibarrRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Repository\LabelProductOrder\LabelProductOrderRepository;

class Order extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private $api;
    private $user;
    private $order;
    private $factorder;
    private $history;
    private $pdf;
    private $colissimo;
    private $label;
    private $labelProductOrder;
    private $productOrder;
    private $notification;
    private $woocommerce;
    private $distributor;
    private $printer;
    private $colissimoConfiguration;
    private $product;
    private $chronopost;
    private $logError;
    private $reassort;
    private $orderDolibarr;
    private $commandeids;

    public function __construct(Api $api, UserRepository $user, 
    OrderRepository $order,
    TransferOrder $factorder,
    HistoryRepository $history,
    CreatePdf $pdf,
    Colissimo $colissimo,
    LabelRepository $label,
    LabelProductOrderRepository $labelProductOrder,
    ProductOrderRepository $productOrder,
    NotificationRepository $notification,
    WoocommerceService $woocommerce,
    DistributorRepository $distributor,
    PrinterRepository $printer,
    ColissimoRepository $colissimoConfiguration,
    ProductRepository $product,
    Chronopost $chronopost,
    LogErrorRepository $logError,
    ReassortRepository $reassort,
    OrderDolibarrRepository $orderDolibarr,
    CommandeidsRepository $commandeids
    ){
      $this->api = $api;
      $this->user = $user;
      $this->order = $order;
      $this->factorder =$factorder;
      $this->history = $history;
      $this->pdf = $pdf;
      $this->colissimo = $colissimo;
      $this->label = $label;
      $this->labelProductOrder = $labelProductOrder;
      $this->productOrder = $productOrder;
      $this->notification = $notification;
      $this->woocommerce = $woocommerce;
      $this->distributor = $distributor;
      $this->printer = $printer;
      $this->colissimoConfiguration = $colissimoConfiguration;
      $this->product = $product;
      $this->chronopost = $chronopost;
      $this->logError = $logError;
      $this->reassort = $reassort;
      $this->orderDolibarr = $orderDolibarr;
      $this->commandeids = $commandeids;
    }
  
    public function orders($id = null, $distributeur = false){

      if($id){
        $orders_user = $this->order->getOrdersByIdUser($id, $distributeur);
        $orderDolibarr = $this->orderDolibarr->getAllOrdersDolibarrByIdUser($id);

        if(count($orderDolibarr['orders']) > 0){
          if(!$distributeur){
            foreach($orderDolibarr['orders'] as $ord){
              // $orders_user['orders'][] = $ord;

              // Met en première position
              array_unshift($orders_user['orders'], $ord);
            }
          }
          $orders_user['count']['order'] = $orders_user['count']['order'] + count($orderDolibarr['orders']);
        }

        return $orders_user;
      } else {
        $status = "processing,order-new-distrib,prepared-order,en-attente-de-pai"; // Commande en préparation
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

        // Liste des distributeurs
        $distributors = $this->distributor->getDistributors();
        $distributors_list = [];
        foreach($distributors as $dis){
          $distributors_list[] = $dis->customer_id;
        }

        // Récupère également les commandes créées depuis dolibarr vers préparation
        $orderDolibarr = $this->orderDolibarr->getAllOrders();
        if(count($orderDolibarr) > 0){
          foreach($orderDolibarr as $ord){
            $orderDolibarr = $this->woocommerce->transformArrayOrderDolibarr($ord);
            $orders[] = $orderDolibarr[0];
          }
        } 


        // Récupère les commandes attribuée en base s'il y en a 
        $orders_distributed = $this->order->getAllOrdersByUsersNotFinished()->toArray(); 
        $ids = array_column($orders_distributed, "order_woocommerce_id");
        $list_orders = [];
        
        if(count($orders_distributed) > 0){
          foreach($orders as $key => $order){
            $take_order = true;
            if(isset($order['shipping_lines'])){
              if(count($order['shipping_lines']) > 0){
                if(str_contains($order['shipping_lines'][0]['method_title'], "Retrait dans notre magasin à Nice")
                  || str_contains($order['shipping_lines'][0]['method_title'], "Retrait dans notre magasin à Marseille")){
                  $take_order = false;
                }
              } 
            } 

            // N'affiche pos les commandes préparées qui sont en réalité finis, du au cache de l'api woocommerce les status sont pas forcément actualisées
            if($order['status'] == "prepared-order"){
              $clesRecherchees = array_keys($ids,  $order['id']);
              if(count($clesRecherchees) == 0){
                $take_order = false;
              }
            }
  
            if($take_order == true){

              // Check if is distributor
              if(in_array($order['customer_id'], $distributors_list)){
                $orders[$key]['is_distributor'] = true;
              } else {
                $orders[$key]['is_distributor'] = false;
              }

              $clesRecherchees = array_keys($ids,  $order['id']);
              
              // Pour les commandes depuis dolibarr
              if(isset($order['from_dolibarr'])){
                $orders[$key]['user_id'] = $orders[$key]['user_id'];
                $orders[$key]['name'] = $orders[$key]['user_id'] ? "" : "Non attribuée";
                $orders[$key]['status'] =  $orders[$key]['status'];
                $orders[$key]['status_text'] = __('status.'.$orders[$key]['status']);
              } else {
                if(count($clesRecherchees) > 0){
                  $orders[$key]['user_id'] =  $orders_distributed[$clesRecherchees[0]]['user_id'];
                  $orders[$key]['name'] =  $orders_distributed[$clesRecherchees[0]]['name'];
                  $orders[$key]['status'] =  $orders_distributed[$clesRecherchees[0]]['status'];
                  $orders[$key]['status_text'] = __('status.'.$orders_distributed[$clesRecherchees[0]]['status']);

                  // Get shipping and billing detail from local data
                  if(isset($orders[$key]['billing'])){
                    $orders[$key]['billing']['first_name'] =  $orders_distributed[$clesRecherchees[0]]['billing_customer_first_name'];
                    $orders[$key]['billing']['last_name'] =  $orders_distributed[$clesRecherchees[0]]['billing_customer_last_name'];
                    $orders[$key]['billing']['company'] =  $orders_distributed[$clesRecherchees[0]]['billing_customer_company'];
                    $orders[$key]['billing']['address_1'] =  $orders_distributed[$clesRecherchees[0]]['billing_customer_address_1'];
                    $orders[$key]['billing']['address_2'] =  $orders_distributed[$clesRecherchees[0]]['billing_customer_address_2'];
                    $orders[$key]['billing']['city'] =  $orders_distributed[$clesRecherchees[0]]['billing_customer_city'];
                    $orders[$key]['billing']['state'] =  $orders_distributed[$clesRecherchees[0]]['billing_customer_state'];
                    $orders[$key]['billing']['postcode'] =  $orders_distributed[$clesRecherchees[0]]['billing_customer_postcode'];
                    $orders[$key]['billing']['country'] =  $orders_distributed[$clesRecherchees[0]]['billing_customer_country'];
                    $orders[$key]['billing']['email'] =  $orders_distributed[$clesRecherchees[0]]['billing_customer_email'];
                    $orders[$key]['billing']['phone'] =  $orders_distributed[$clesRecherchees[0]]['billing_customer_phone'];
                  }

                  if(isset($orders[$key]['shipping'])){
                    $orders[$key]['shipping']['first_name'] =  $orders_distributed[$clesRecherchees[0]]['shipping_customer_first_name'];
                    $orders[$key]['shipping']['last_name'] =  $orders_distributed[$clesRecherchees[0]]['shipping_customer_last_name'];
                    $orders[$key]['shipping']['company'] =  $orders_distributed[$clesRecherchees[0]]['shipping_customer_company'];
                    $orders[$key]['shipping']['address_1'] =  $orders_distributed[$clesRecherchees[0]]['shipping_customer_address_1'];
                    $orders[$key]['shipping']['address_2'] =  $orders_distributed[$clesRecherchees[0]]['shipping_customer_address_2'];
                    $orders[$key]['shipping']['city'] =  $orders_distributed[$clesRecherchees[0]]['shipping_customer_city'];
                    $orders[$key]['shipping']['state'] =  $orders_distributed[$clesRecherchees[0]]['shipping_customer_state'];
                    $orders[$key]['shipping']['postcode'] =  $orders_distributed[$clesRecherchees[0]]['shipping_customer_postcode'];
                    $orders[$key]['shipping']['country'] =  $orders_distributed[$clesRecherchees[0]]['shipping_customer_country'];
                    $orders[$key]['shipping']['phone'] =  $orders_distributed[$clesRecherchees[0]]['shipping_customer_phone'];
                  }

                } else {
                  $orders[$key]['user_id'] = null;
                  $orders[$key]['name'] = "Non attribuée";
                  $orders[$key]['status'] =  $orders[$key]['status'];
                  $orders[$key]['status_text'] = __('status.'.$orders[$key]['status']);
                }
              }
             
              $list_orders[] = $orders[$key];
            }
       
          }
        } else {
          foreach($orders as $key => $order){

            // Check if is distributor
            if(in_array($order['customer_id'], $distributors_list)){
              $orders[$key]['is_distributor'] = true;
            } else {
              $orders[$key]['is_distributor'] = false;
            }

            if(isset($order['shipping_lines'])){
              if(count($order['shipping_lines']) > 0){
                if(str_contains($order['shipping_lines'][0]['method_title'], "Retrait dans notre magasin à Nice")
                  || str_contains($order['shipping_lines'][0]['method_title'], "Retrait dans notre magasin à Marseille")){
                  $list_orders[] = $order;
                }
              } else {
                $list_orders[] = $order;
              }
            } else {
              $list_orders[] = $order;
            }
          }
        }

        return $list_orders;
      }
    } 


    public function getOrder(){
      return $this->orders(Auth()->user()->id);
    }

    public function getAllOrders(){
      // Préparateur
      $users =  $this->user->getUsersAndRoles();
      $products_pick =  $this->productOrder->getAllProductsPicked()->toArray();
      $products_pick_dolibarr =  $this->orderDolibarr->getAllProductsPickedDolibarr();

      if(count($products_pick_dolibarr) > 0){
        $products_pick = array_merge($products_pick, $products_pick_dolibarr);
      }

      $status_list = __('status_order');
      echo json_encode(['orders' => $this->orders(), 'users' => $users, 'products_pick' => $products_pick, 'status_list' => $status_list]);
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
      $orders_to_update = [];
      $list_preparateur = [];

      foreach($users as $user){
        $array_user[$user['user_id']] = [];
        $list_preparateur[] = $user['user_id'];
      }

      if(count($array_user) == 0){
        echo json_encode(['success' => false, 'message' => 'Il n\'y a pas de préparateurs !']);
        return;
      }

      // Liste des commandes déjà réparties entres les utilisateurs
      $orders_user = $this->order->getAllOrdersByUsersNotFinished()->toArray();
      $orderDolibarr = $this->orderDolibarr->getAllOrders();
      if(count($orderDolibarr) > 0){
        foreach($orderDolibarr as $ord){
          $orderDolibarr = $this->woocommerce->transformArrayOrderDolibarr($ord);
          $orders_user[] = $orderDolibarr[0];
        }
      } 

      foreach($orders_user as $order){
        if(in_array($order['user_id'], $list_preparateur)){
          $array_user[$order['user_id']][] =  $order;
          $orders_id [] = $order['order_woocommerce_id'];
        }
      }



      // Liste des commandes Woocommerce
      $orders = $this->orders();

      $ids = array_column($orders, "id");
      foreach($orders_id as $id){
        $clesRecherchees = array_keys($ids,  $id);
        if(count($clesRecherchees) > 0){
          $orders_to_delete [] = $id;
        } else if(count($clesRecherchees) == 0) {
          $orders_to_update [] = $id;
        }
      }

      // Modifie le status des commandes qui ne sont plus en cours dans woocommerce
      if(count($orders_to_update) > 0){
        $this->order->updateOrdersById($orders_to_update);
      }

      if(count($array_user) > 0){
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

        // Supprime du tableau les commandes à ne pas prendre en compte si déjà attribuées
        foreach($array_user as $key => $array){
          foreach($array as $key2 => $arr){
            if(isset($arr['order_woocommerce_id'])){
              if(in_array($arr['order_woocommerce_id'], $orders_to_delete)){
                unset($array_user[$key][$key2]);
              }
            } else if(isset($arr['id'])){
              if(in_array($arr['id'], $orders_to_delete)){
                unset($array_user[$key][$key2]);
              }
            }
          }
        }
        
        // Liste des distributeurs
        $distributors = $this->distributor->getDistributors();
        $distributors_list = [];
        foreach($distributors as $dis){
          $distributors_list[] = $dis->customer_id;
        }
 
        $this->order->insertOrdersByUsers($array_user, $distributors_list);
      }
    }

    // Désattribue toutes les commandes
    public function unassignOrders(){
      if($this->orderDolibarr->unassignOrdersDolibarr()){
        $this->order->unassignOrders();
      } else {
        echo json_encode(['success' => false, 'message' => 'Error']);
      }
    }

    public function ordersPrepared(Request $request){
      $barcode_array = $request->post('pick_items');
      $products_quantity = $request->post('pick_items_quantity');
      $order_id = $request->post('order_id');
      $partial = $request->post('partial');
      $note_partial_order = $request->post('note_partial_order') ?? null;
      $from_dolibarr = $request->post('from_dolibarr') == "true" ? true : false;
      $from_transfers = $request->post('from_transfers') == "true" ? true : false;

      if($barcode_array != "false" && $order_id && $products_quantity != "false"){
        if($from_dolibarr){
          if($barcode_array != null){
            $check_if_order_done = $this->orderDolibarr->checkIfDoneOrderDolibarr($order_id, $barcode_array, $products_quantity, intval($partial));
          } else if($partial == "1" && $barcode_array == null){
            $this->orderDolibarr->updateOneOrderStatus("waiting_to_validate", $order_id);
            $check_if_order_done = true;
          }
        } else if($from_transfers){
          if($barcode_array != null){
            $check_if_order_done = $this->reassort->checkIfDoneTransfersDolibarr($order_id, $barcode_array, $products_quantity, intval($partial));
          } else if($partial == "1" && $barcode_array == null){
            $this->reassort->updateStatusTextReassort($order_id ,"waiting_to_validate");;
            $check_if_order_done = true;
          }
        } else {
          if($barcode_array != null){
            $check_if_order_done = $this->order->checkIfDone($order_id, $barcode_array, $products_quantity, intval($partial));
          } else if($partial == "1" && $barcode_array == null){
            $this->order->updateOrdersById([$order_id], "waiting_to_validate");
            $check_if_order_done = true;
          }
        }

        if($check_if_order_done && $partial == "1"){
          
          // Récupère les chefs d'équipes
          $leader = $this->user->getUsersByRole([4]);
          $from_user = Auth()->user()->id;
          foreach($leader as $lead){
              // Insert dans notification
              $data = [
                'from_user' => $from_user,
                'to_user' => $lead['user_id'],
                'type' => 'partial_prepared_order',
                'order_id' => $order_id,
                'detail' => $note_partial_order ?? "La commande #".$order_id." est incomplète"
              ];

              $this->notification->insert($data);
          }

          // Pusher notification partial order  
          $notification_push = [
            'role' => 4,
            'order_id' => $order_id,
            'type' => 'partial_order',
            'data' => $note_partial_order ?? "La commande #".$order_id." est incomplète"
          ];
          event(New NotificationPusher($notification_push));
        }
        echo json_encode(["success" => $check_if_order_done]);
      } else {
        // Check if all products are picked
        $picked = true;
        if($from_transfers){
          $products = $this->reassort->getReassortById($order_id);
          foreach($products as $p){
            if($p->qty != $p->pick){
              $picked = false;
            }
          }
        } else {
          $products = $this->productOrder->getProductsByOrderId($order_id);
          foreach($products as $p){
            if($p->quantity != $p->pick){
              $picked = false;
            }
          }
        }

        if($picked && $from_dolibarr){
            $this->orderDolibarr->updateOneOrderStatus("prepared-order", $order_id);
            echo json_encode(["success" => true]);
            return;
        } else if($picked && $from_transfers){
            $this->reassort->updateStatusTextReassort($order_id ,"prepared-order");
            echo json_encode(["success" => true]);
            return;
        } else if($picked && !$from_transfers && !$from_dolibarr){
          $this->order->updateOrdersById([$order_id], "prepared-order");
          $this->api->updateOrdersWoocommerce("prepared-order", $order_id);
          echo json_encode(["success" => true]);
          return;
        }

        echo json_encode(["success" => $picked]);
      }
    }
    
    // public function transfersPrepared(Request $request){
    //   $barcode_array = $request->post('pick_items');
    //   $products_quantity = $request->post('pick_items_quantity');
    //   $order_id = $request->post('order_id');

    //   if($barcode_array != null){
    //     $check = $this->reassort->checkIfDone($order_id, $barcode_array, $products_quantity);
    //   }
     
    //   echo json_encode(["success" => $check]);
    // }

    public function ordersReset(Request $request){
      $order_id = $request->post('order_id');
      $from_dolibarr = $request->post('from_dolibarr') == "true" ? true : false;
      $from_transfers = $request->post('from_transfers') == "true" ? true : false;

      if($from_dolibarr){
        $orderReset = $this->orderDolibarr->orderResetDolibarr($order_id);
      } else if($from_transfers){
        $orderReset = $this->reassort->orderResetTransfers($order_id);
      } else {
        $orderReset = $this->order->orderReset($order_id);
      }

      echo json_encode(["success" => $orderReset]);
    }

    public function updateAttributionOrder(Request $request){
      $from_user = $request->post('from_user');
      $to_user = $request->post('to_user');

      if($from_user && $to_user){
        try{
          // Update order woocommerce
          $this->order->updateOrderAttribution($from_user, $to_user);
          // Update order dolibarr
          $this->orderDolibarr->updateOrderAttributionDolibarr($from_user, $to_user);

          echo json_encode(["success" => $this->order->updateOrderAttribution($from_user, $to_user)]);
        } catch(Exception $e){
          echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
      } else {
        echo json_encode(["success" => false]);
      }
    }


    public function updateOneOrderAttribution(Request $request){
      $order_id = $request->post('order_id');
      $user_id = $request->post('user_id');
      $from_dolibarr = $request->post('from_dolibarr');
      $is_distributor = $request->post('is_distributor') ?? false;

      if($order_id && $user_id){
        if($from_dolibarr == "false"){
          $update = $this->order->updateOneOrderAttribution($order_id, $user_id, $is_distributor);
        } else {
          $update = $this->orderDolibarr->updateOneOrderAttributionDolibarr($order_id, $user_id);
        }


        $number_order_attributed = $this->order->getOrdersByUsers();

         // Pusher notification order attribution updated  
         $notification_push = [
          'role' => 2,
          'order_id' => $order_id,
          'type' => 'order_attribution_updated',
        ];
        event(New NotificationPusher($notification_push));

        echo json_encode(["success" => $update, 'number_order_attributed' => count($number_order_attributed)]);
      } else {
        echo json_encode(["success" => false]);
      }
    }


    public function updateOrderStatus(Request $request){
      $order_id = $request->post('order_id');
      $status = $request->post('status');
      $user_id = $request->post('user_id');
      $from_dolibarr = $request->post('from_dolibarr');

      if($order_id && $status){

        // Si pas de user récupéré
        if($user_id == null && ($from_dolibarr == "false" || $from_dolibarr == "0")){
          $order_details = $this->order->getOrderById($order_id);

          if(count($order_details) > 0){
            $user_id = $order_details[0]['user_id'];
          } 
        }

        if($status == "waiting_validate" && $user_id != null){
          $data = [
            'from_user' => Auth()->user()->id,
            'to_user' => $user_id,
            'type' => 'partial_prepared_order_validate',
            'order_id' => $order_id,
            'detail' => "Vous pouvez reprendre la commande #".$order_id
          ];

          $this->notification->insert($data);
        }
        $number_order_attributed = $this->order->getOrdersByUsers();

        // Update status woocommerce selon le status, en cours, terminée ou commande nouveau distrib
        $ignore_status = ['waiting_to_validate', 'waiting_validate', 'partial_prepared_order', 'partial_prepared_order_validate'];


        if($from_dolibarr == "false" || $from_dolibarr == "0"){
          if(!in_array($status,  $ignore_status)){
            if($status == "finished"){
              $this->api->updateOrdersWoocommerce("lpc_ready_to_ship", $order_id);
            } else {
              $this->api->updateOrdersWoocommerce($status, $order_id);
            } 
          }
          echo json_encode(["success" => $this->order->updateOrdersById([$order_id], $status), 'number_order_attributed' => count($number_order_attributed)]);
        } else {
          $update = $this->orderDolibarr->updateOneOrderStatus($status, $order_id);
          echo json_encode(["success" => $update, 'number_order_attributed' => count($number_order_attributed)]);
        }

      } else {
        echo json_encode(["success" => false]);
      }
    }

    public function orderReInvoicing(Request $request){
      $order_id = $request->post('order_id');

      try{
        // Update order re invoicing
        $this->commandeids->deleteOrder($order_id);
        echo json_encode(["success" => true]);
      } catch(Exception $e){
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
      }
    }

    public function checkExpedition(Request $request){
      $order_id = explode(',', $request->post('order_id'))[0];
      $order = $this->order->getOrderById($order_id);

      if($order){
        // Check si commande distributeur, si oui rebipper les produits
        $is_distributor = false; //$this->distributor->getDistributorById($order[0]['customer_id']) != 0 ? true : false;
        echo json_encode(['success' => true, 'transfers'=> false, 'from_dolibarr' => false, 'order' => $order, 'is_distributor' => $is_distributor, 'status' =>  __('status.'.$order[0]['status'])]);
      } else {
        // Check si commande dolibarr
        $order = $this->orderDolibarr->getOrdersDolibarrById($order_id)->toArray();
        if(count($order) > 0){
          echo json_encode(['success' => true, 'transfers'=> false, 'from_dolibarr' => true, 'order' => $order, 'is_distributor' => false, 'status' =>  __('status.'.$order[0]['status'])]);
        } else {
          $order = $this->reassort->getReassortById($order_id);
          if(count($order) > 0){
          // Check si commande est un transfert
          echo json_encode(['success' => true, 'transfers'=> true, 'from_dolibarr' => false, 'order' => $order, 'is_distributor' => false, 'status' =>  __('status.'.$order[0]['status'])]);
          } else {
            echo json_encode(['success' => false, 'message' => 'Aucune commande ne correspond à ce numéro']);
          }
        }
      }
    }

    public function validWrapOrder(Request $request){
      
      
      $from_dolibarr = $request->post('from_dolibarr') == "false" ? 0 : 1;
      $transfers = $request->post('transfers') == "false" ? 0 : 1;
      // Sécurité dans le cas ou tout le code barre est envoyé, on récupère que le numéro.
      $order_id = explode(',', $request->post('order_id'))[0];


      // $from_dolibarr=false;
      // $transfers=false;
      // $order_id ="107096";




      if($from_dolibarr){
        // Si commande dolibarr je fournis le fk_command
        $order = $this->orderDolibarr->getOrdersDolibarrById($order_id);
      } else if($transfers){
        // Si transfert, envoyé les données à Lyes pour le valider (id transfert)
        return $this->executerTransfere($order_id);
      } else {
        $order = $this->order->getOrderByIdWithCustomer($order_id);
      }



      if($order && count($order) > 0){
        if($order[0]['status'] == "finished" || $order[0]['status'] == "lpc_ready_to_ship"){
          echo json_encode(["success" => false, "message" => "Cette commande est déjà emballée !"]);
          return;
        }

        $is_distributor = false; //$order[0]['is_distributor'] != null ? true : false;
        if($is_distributor && !$from_dolibarr){
          $barcode_array = $request->post('pick_items');
          $products_quantity = $request->post('pick_items_quantity');
          $check_if_order_done = $this->order->checkIfValidDone($order_id, $barcode_array, $products_quantity);

          if(!$check_if_order_done){
            echo json_encode(["success" => false, "message" => "Veuillez vérifier tous les produits !", "verif" => true]);
            return;
          }
        }
        
        if($from_dolibarr){
          $orders = $this->woocommerce->transformArrayOrderDolibarr($order);
        } else {
          $orders = $this->woocommerce->transformArrayOrder($order);
        }

        $orders[0]['emballeur'] = Auth()->user()->name;
        // envoi des données pour créer des facture via api dolibar....
        try{
            $this->factorder->Transferorder($orders);

            // Insert la commande dans histories
            $data = [
              'order_id' => $order_id,
              'user_id' => Auth()->user()->id,
              'status' => 'finished',
              'poste' => Auth()->user()->poste,
              'created_at' => date('Y-m-d H:i:s')
            ];

            $this->history->save($data);
           

            if($from_dolibarr){
              $this->orderDolibarr->updateOneOrderStatus("finished", $order_id);
            } else {
              // Status différent selon type de commande
              $status_finished = "lpc_ready_to_ship";
              if(isset($orders[0]['shipping_method'])){
                if($orders[0]['shipping_method'] == "local_pickup" && $orders[0]['is_distributor']){
                  $status_finished = "commande-distribu";
                } 
              }
              // Modifie le status de la commande sur Woocommerce en "Prêt à expédier"
              $this->order->updateOrdersById([$order_id], "finished");
              $this->api->updateOrdersWoocommerce($status_finished, $order_id);
            }
        } catch(Exception $e){
          $this->logError->insert(['order_id' => $order_id, 'message' => $e->getMessage()]);
          echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
          echo json_encode(['success' => true, 'message' => 'Commande '.$order[0]['order_woocommerce_id'].' préparée avec succès !']);
      } else {
          echo json_encode(['success' => false, 'message'=> 'Aucune commande associée, vérifiez l\'id de la commande !']);
      }
    }

    // Historique commande préparateur
    public function ordersHistory(){
      $history = $this->order->getHistoryByUser(Auth()->user()->id);
      $printer = $this->printer->getPrinterByUser(Auth()->user()->id);
      return view('preparateur.history', ['history' => $history, 'printer' => $printer[0] ?? false]);
    }

    // Fonction à appelé après validation d'une commande
    // private function generateLabel($order){

    //   $colissimo = $this->colissimoConfiguration->getConfiguration();
    //   $product_to_add_label = [];
    //   $quantity_product = [];

    //   if($order){
    //     $weight = 0; // Kg

    //     foreach($order[0]['line_items'] as $or){
    //       $weight = $weight + number_format(($or['weight'] *$or['quantity']), 2);
    //       $product_to_add_label[] = $or['product_id'];
    //       $quantity_product[$or['product_id']] = $or['quantity'];
    //     } 


    //     if(str_contains($order[0]['shipping_method'], 'chrono')){
    //       $labelChrono = $this->chronopost->generateLabelChrono($order[0], $weight, $order[0]['order_woocommerce_id'], count($colissimo) > 0 ? $colissimo[0] : null);
    //       if(isset($labelChrono['success'])){
    //           $labelChrono['label'] = mb_convert_encoding($labelChrono['label'], 'ISO-8859-1');
    //           $insert_label = $this->label->save($labelChrono);
    //           $insert_product_label_order = $this->labelProductOrder->insert($order[0]['order_woocommerce_id'], $insert_label, $product_to_add_label, $quantity_product);
    //       } else {
    //           return redirect()->route('labels')->with('error', $labelChrono);
    //       }
    //     } else {
    //       $label = $this->colissimo->generateLabel($order[0], $weight, $order[0]['order_woocommerce_id'], count($colissimo) > 0 ? $colissimo[0] : null);

    //       if(isset($label['success'])){
    //         $label['label'] =  mb_convert_encoding($label['label'], 'ISO-8859-1');
    //         $label['cn23'] != null ? mb_convert_encoding($label['cn23'], 'ISO-8859-1') : $label['cn23'];
    //         $insert_label = $this->label->save($label);
    //         $insert_product_label_order = $this->labelProductOrder->insert($order[0]['order_woocommerce_id'], $insert_label, $product_to_add_label, $quantity_product);
  
    //         if(is_int($insert_label) && $insert_label != 0 && $insert_product_label_order){
  
    //           // ----- Print label to printer Datamax -----
    //           if($label['label_format'] == "ZPL"){
    //             echo json_encode(['success' => true, 'file' => base64_encode($label['label']), 'message'=> 'Étiquette générée pour la commande '.$order[0]['order_woocommerce_id']]);
    //           } else if($label['label_format'] == "PDF"){
    //             return base64_encode($label['label']);
    //           } else {
    //             echo json_encode(['success' => true, 'message'=> 'Étiquette générée pour la commande '.$order[0]['order_woocommerce_id']]);
    //           }
    //           // ----- Print label to printer Datamax -----
  
    //         } else {
    //           echo json_encode(['success' => false, 'message'=> 'Étiquette générée et disponible sur Woocommerce mais erreur base préparation !']);
    //         }
    //       } else {
    //         echo json_encode(['success' => false, 'message'=> 'Commande validée mais erreur génération d\'étiquette : '.$label]);
    //       }
    //     }
  
    //   }
    // }

    public function leaderHistory(){
      $histories = $this->history->getAllHistory();
      $histories_order = [];

      foreach($histories as $history){
        if(isset($histories_order[$history['order_id']])){
          if($histories_order[$history['order_id']]['status'] == "prepared"){
            // $histories_order[$history['order_id']]['order_status'] = $histories_order[$history['order_id']]['order_status'];
            $histories_order[$history['order_id']]['prepared'] = $histories_order[$history['order_id']]['name'];
            $histories_order[$history['order_id']]['finished'] = $history['name'];
            $histories_order[$history['order_id']]['prepared_date'] = date('d/m/Y H:i', strtotime($histories_order[$history['order_id']]['created_at']));
            $histories_order[$history['order_id']]['finished_date'] = date('d/m/Y H:i', strtotime($history['created_at']));
          } else {
            // $histories_order[$history['order_id']]['order_status'] = $histories_order[$history['order_id']]['order_status'];
            $histories_order[$history['order_id']]['prepared'] = $history['name'];
            $histories_order[$history['order_id']]['finished'] = $histories_order[$history['order_id']]['name'];
            $histories_order[$history['order_id']]['finished_date'] = date('d/m/Y H:i', strtotime($histories_order[$history['order_id']]['created_at']));
            $histories_order[$history['order_id']]['prepared_date'] = date('d/m/Y H:i', strtotime($history['created_at']));
          }
        } else {
          $histories_order[$history['order_id']] = $history;
          // $histories_order[$history['order_id']]['prepared'] = $history['order_status'];
          $history['status'] == 'prepared' ? $histories_order[$history['order_id']]['user_id_prepared'] = $history['id'] : '';
          $histories_order[$history['order_id']]['prepared'] = $history['status'] == 'prepared' ? $history['name'] : null;
          $histories_order[$history['order_id']]['finished'] = $history['status'] == 'finished' ? $history['name'] : null;
          $histories_order[$history['order_id']]['finished_date'] = $history['status'] == 'finished' ? date('d/m/Y H:i', strtotime($history['created_at'])) : null;
          $histories_order[$history['order_id']]['prepared_date'] = $history['status'] == 'prepared' ? date('d/m/Y H:i', strtotime($history['created_at'])) : null;
        } 
      }

      return view('leader.history', ['histories' => $histories_order, 'list_status' => __('status_order')]);
    }

    public function generateHistory(Request $request){
      $date = $request->post('date_historique');
      $histories = $this->history->getHistoryByDate($date);
      $list_histories = [];

      if(count($histories) == 0){
        return redirect()->route('leader.history')->with('error', 'Aucun historique pour la date sélectionnée '.$date);
      }

      foreach($histories as $key => $histo){

          if(!isset($list_histories[$histo['id']])){
            $list_histories[$histo['id']] = [
              'name' => $histo['name'],
              'poste' => [$histo['poste']],
              'prepared_order' => $histo['status'] == "prepared" ? [$histo['order_id']] : [],
              'finished_order' => $histo['status'] == "finished" ? [$histo['order_id']] : [],
              'prepared_count' => $histo['status'] == "prepared" ? 1 : 0,
              'finished_count' => $histo['status'] == "finished" ? 1 : 0,
              'items_picked' =>  $histo['status'] == "prepared" ? $histo['quantity'] : 0
            ];
          } else {
            $histo['status'] == "prepared" ? array_push($list_histories[$histo['id']]['prepared_order'],$histo['order_id']) : array_push($list_histories[$histo['id']]['finished_order'],$histo['order_id']);
            $list_histories[$histo['id']]['poste'][] = $histo['poste'];
            
            $list_histories[$histo['id']]['prepared_order'] = array_unique($list_histories[$histo['id']]['prepared_order']);
            $list_histories[$histo['id']]['finished_order'] = array_unique($list_histories[$histo['id']]['finished_order']);

            $list_histories[$histo['id']]['poste'] = array_unique($list_histories[$histo['id']]['poste']);

            $list_histories[$histo['id']]['prepared_count'] = count($list_histories[$histo['id']]['prepared_order']);
            $list_histories[$histo['id']]['finished_count'] = count($list_histories[$histo['id']]['finished_order']);
            $histo['status'] == "prepared" ? $list_histories[$histo['id']]['items_picked'] = $list_histories[$histo['id']]['items_picked'] + $histo['quantity'] : '';
          }
      }
      
      
      // Générer mon pdf
      return $this->pdf->generateHistoryOrders($list_histories, $date);
    }

    public function closeDay(){
      $date = date('Y-m-d');
      $histories = $this->history->getHistoryByDate($date);
      $list_histories = [];

      if(count($histories) == 0){
        return redirect()->route('index')->with('error', 'Aucune commande préparée ou emballée n\'a été trouvée !');
      }

      foreach($histories as $key => $histo){
          if(!isset($list_histories[$histo['id']])){
            $list_histories[$histo['id']] = [
              'name' => $histo['name'],
              'poste' => [$histo['poste']],
              'prepared_order' => $histo['status'] == "prepared" ? [$histo['order_id']] : [],
              'finished_order' => $histo['status'] == "finished" ? [$histo['order_id']] : [],
              'prepared_count' => $histo['status'] == "prepared" ? 1 : 0,
              'finished_count' => $histo['status'] == "finished" ? 1 : 0,
              'items_picked' =>  $histo['status'] == "prepared" ? $histo['quantity'] : 0
            ];
          } else {
            $histo['status'] == "prepared" ? array_push($list_histories[$histo['id']]['prepared_order'],$histo['order_id']) : array_push($list_histories[$histo['id']]['finished_order'],$histo['order_id']);
            $list_histories[$histo['id']]['poste'][] = $histo['poste'];
            
            $list_histories[$histo['id']]['prepared_order'] = array_unique($list_histories[$histo['id']]['prepared_order']);
            $list_histories[$histo['id']]['finished_order'] = array_unique($list_histories[$histo['id']]['finished_order']);

            $list_histories[$histo['id']]['poste'] = array_unique($list_histories[$histo['id']]['poste']);

            $list_histories[$histo['id']]['prepared_count'] = count($list_histories[$histo['id']]['prepared_order']);
            $list_histories[$histo['id']]['finished_count'] = count($list_histories[$histo['id']]['finished_order']);
            $histo['status'] == "prepared" ? $list_histories[$histo['id']]['items_picked'] = $list_histories[$histo['id']]['items_picked'] + $histo['quantity'] : '';
          }
      }

      return $this->pdf->generateHistoryOrders($list_histories, $date);
    }

    public function leaderHistoryOrder(){
      $history = $this->order->getAllHistory();
      $printer = $this->printer->getPrinterByUser(Auth()->user()->id);
      // Renvoie la vue historique du préparateurs mais avec toutes les commandes de chaque préparateurs
      return view('preparateur.history', ['history' => $history, 'printer' => $printer[0] ?? false]);
    }

    public function deleteOrderProducts(Request $request){
      $order_id = $request->post('order_id');
      $line_item_id = $request->post('line_item_id');
      $increase = $request->post('increase');
      $quantity = $request->post('quantity');
      $product_id = $request->post('product_id');


      //Supprimer de ma base en local le produit lié à la commande
      $delete_product = $this->productOrder->deleteProductOrderByLineItem($order_id, $line_item_id);
      //Supprimer de la commande via api woocommerce
      $delete = $this->api->deleteProductOrderWoocommerce($order_id, $line_item_id, $increase, $quantity, $product_id);
      // Update le total de la commande en base de données
      if(is_array($delete)){
        $update_order = $this->order->updateTotalOrder($order_id, $delete);
        echo json_encode(['success' => true, 'order' => $delete]);
      } else {
        echo json_encode(['success' => false]);
      }
    }

    public function deleteOrderProductsDolibarr(Request $request){
      $order_id = $request->post('order_id');
      $quantity_to_delete = $request->post('quantity_to_delete');
      $quantity = $request->post('quantity');
      $product_dolibarr_id = $request->post('product_dolibarr_id');

      if($quantity_to_delete >= $quantity){
        // Suppression produit
        $delete_product = $this->orderDolibarr->deleteProductOrder($order_id, $product_dolibarr_id);
        echo json_encode(['success' => $delete_product]);
      } else {
        // Update produit
        $update_product = $this->orderDolibarr->updateProductOrder($order_id, $product_dolibarr_id, ['qte' => intval($quantity) - intval($quantity_to_delete)]);
        echo json_encode(['success' => $update_product]);
      }
    }

    public function addOrderProducts(Request $request){
      $order_id = $request->post('order_id');
      $product = $request->post('product');
      $quantity = $request->post('quantity');

      if($quantity < 1){
        $quantity = 1;
      }

      $product_order_woocommerce = $this->api->addProductOrderWoocommerce($order_id, $product , $quantity);

      if(is_array($product_order_woocommerce)){
        $update_order = $this->order->updateTotalOrder($order_id, $product_order_woocommerce);
        $insert_product_order = $this->productOrder->insertProductOrder($product_order_woocommerce);

        echo json_encode(['success' => $insert_product_order, 'order' => $product_order_woocommerce]); 
      } else {
        echo json_encode(['success' => false]); 
      }
    }


    public function checkProductBarcode(Request $request){
      $product_id = $request->post('product_id');
      $barcode = $request->post('barcode');
      $barcode_valid = $this->product->checkProductBarcode($product_id, $barcode);
      
      if($barcode_valid == 1){
        echo json_encode(['success' => true]);
      } else {
        echo json_encode(['success' => false]);
      }
    }

    public function checkProductBarcodeForTransfers(Request $request){
      $product_id = $request->post('product_id');
      $barcode = $request->post('barcode');
      $barcode_valid = $this->reassort->checkProductBarcode($product_id, $barcode);
      
      if($barcode_valid > 0){
        echo json_encode(['success' => true]);
      } else {
        echo json_encode(['success' => false]);
      }
    }

    public function executerTransfere($identifiant_reassort){

      try {
          $tabProduitReassort = $this->reassort->findByIdentifiantReassort($identifiant_reassort);
          if (!$tabProduitReassort) {
              echo json_encode(['success' => false, 'message' => "Transfère introuvable".$identifiant_reassort]);
              return;
              // return ["response" => false, "error" => "Transfère introuvable".$identifiant_reassort];
          }
          
          $apiKey = env('KEY_API_DOLIBAR');   
          $apiUrl = env('KEY_API_URL');
        
          $data_save = array();
          $incrementation = 0;
          $decrementation = 0;
          $i = 1;
          $ids="";
          $updateQuery = "UPDATE prepa_hist_reassort SET id_reassort = CASE";
          foreach ($tabProduitReassort as $key => $line) {

              

              if ($line["qty"] != 0) {           
                  $data = array(
                      'product_id' => $line["product_id"],
                      'warehouse_id' => $line["warehouse_id"], 
                      'qty' => $line["qty"]*1, 
                      'type' => $line["type"], 
                      'movementcode' => $line["movementcode"], 
                      'movementlabel' => $line["movementlabel"], 
                      'price' => $line["price"], 
                      'datem' => date("Y-m-d", strtotime($line["datem"])), 
                      'dlc' => date("Y-m-d", strtotime($line["dlc"])),
                      'dluo' => date("Y-m-d", strtotime($line["dluo"])),
                  );
                  // on execute le réassort
                  $stockmovements = $this->api->CallAPI("POST", $apiKey, $apiUrl."stockmovements",json_encode($data));

                  if ($stockmovements) {
                      $updateQuery .= " WHEN id = ".$line['id']. " THEN ". $stockmovements;
                      if (count($tabProduitReassort) != $i) {
                          $ids .= $line['id'] . ",";
                      }else{
                          $ids .= $line['id'];
                      }
                      $i++;  
                      $incrementation++;
                  }
              }
          }
          $updateQuery .= " ELSE -1 END WHERE id IN (".$ids.")";
          $response = DB::update($updateQuery);

          // Update status transfers
          $colonnes_values = ['status' => "finished"];
          $res = $this->reassort->update_in_hist_reassort($identifiant_reassort, $colonnes_values);

          // Insert la commande dans histories
          $data = [
            'order_id' => $identifiant_reassort,
            'user_id' => Auth()->user()->id,
            'status' => 'finished',
            'poste' => Auth()->user()->poste,
            'created_at' => date('Y-m-d H:i:s')
          ];

          $this->history->save($data);

          echo json_encode(['success' => true, 'message' => 'Transfert '.$identifiant_reassort.' transféré avec succès !']);
      } catch (\Throwable $th) {
          // dd($th);
          echo json_encode(['success' => false, 'message' => $th->getMessage()]);
          // return ["response" => false, "error" => $th->getMessage()];
      } 
  }

  public function updateDetailsOrders(Request $request){
    $order_id = $request->post('order_id');
    $field = $request->post('field');
    $field_value = $request->post('field_value');

    if($order_id && $field && $field_value){
        $data = [
          $field => $field_value
        ];
  
        $this->order->update($data, $order_id);
    } else {
      echo json_encode(['success' => false]);
    }

  }

  function updateStockWoocommerce($identifiant_reassort){

    $data = $this->reassort->getQteToTransfer($identifiant_reassort);

    // Enregistrez le temps de début
    $datas_updated_succes = array();
    $datas_updated_error = array();

    // Récupérer les ids produit de woocommerce
    $ids_woocomerce = $this->product->getProductsByBarcode($data);

    // dd($ids_woocomerce);

    if ($ids_woocomerce["response"]) {
      // on fait l'actualisation sur woocommerce
      $datas = $ids_woocomerce["ids_wc_vs_qte"];

      // dd($datas);
      foreach ($datas as $key => $data) {
        // filtrer les kits comme les limes et construire les lots
        $product_id_wc = $data["id_product_wc"];
        $quantity = $data["qty"];

        $update_response =  $this->product->updateStockServiceWc($product_id_wc, $quantity);
        if ($update_response["response"]) {
          $data["qte_actuelle"] = $update_response["qte_actuelle"];
          array_push($datas_updated_succes,$data);
        }else {
          $data["qte_actuelle"] = $update_response["qte_actuelle"];
          array_push($datas_updated_error,$data);
        }

      }

      if ($datas_updated_succes) {
        if ($datas_updated_error) {
          return redirect()->back()->with('success', 'La synchronisation des quantités sur le site a réussit mais pas a 100%');
        }else {
          return redirect()->back()->with('success', 'La synchronisation des quantités sur le site a réussit');
        }
      }else {
        return redirect()->back()->with('error',  "La synchronisation n'a pas fonctionné");
      }

    }else {
      return redirect()->back()->with('error',  $ids_woocomerce["message"]);
    }


  }


  public function getDetailsOrder(Request $request){
    $order_id = $request->post('order_id');

    if(str_contains($order_id, 'CO')){
      $order = $this->orderDolibarr->getOrdersDolibarrById($order_id)->toArray();
    } else if(strlen($order_id) == 10){
      $order = $this->reassort->getReassortById($order_id);
    } else {
      $order = $this->order->getOrderById($order_id);
    }

    if($order){
      echo json_encode(['success' => true, 'order' => $order]);
    } else {
      echo json_encode(['success' => false]);
    }
  }

  function constructKit(Request $request){

    // Récuperer les produits appartenant a la catégorie 100 (Limes)
   
   

    $id_categorie = $request->post('id_categorie');

    // return $id_categorie;
    // dd($id_categorie);


    // $id_categorie = 70;  // Limes
    // $id_categorie = 70;  // Vernis semi permanent Elya Maje
    $products_unite =  $this->reassort->getProductsByCategorie($id_categorie);
    $products_association_by_ids = $this->reassort->productsAssociationByIds($products_unite, $id_categorie);

    return $products_association_by_ids;
    
    dd("var");

    $id_categories = 100;



  }

  function validateKits(Request $request){

    try {
      $id_wc = $request->post('id_wc');
      $qty = $request->post('qty');

      $res = $this->reassort->putQuantiteInWc($id_wc, $qty);

    return $res;
    } catch (\Throwable $th) {
      return $th->getMessage();
    }

    

  }

  
  function uploadFile(Request $request){

    if ($request->hasFile('file_reassort') && $request->file('file_reassort')->isValid()) {
        $file = $request->file('file_reassort');

        $csvContent = $file->getContent();
        $reader = Reader::createFromString($csvContent);
        $reader->setHeaderOffset(0);

        $csvDataArray = iterator_to_array($reader->getRecords());

        dd($csvDataArray);

        
    }

    // Retournez une réponse en cas d'erreur
    return response()->json(['message' => 'Erreur lors du téléchargement du fichier CSV'], 400);
  }
}


