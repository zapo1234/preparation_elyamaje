<?php

namespace App\Http\Controllers;

use stdClass;
use Exception;
use League\Csv\Reader;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
// use Illuminate\Support\Facades\Mail;
use App\Events\NotificationPusher;
use Illuminate\Support\Facades\DB;
use App\Http\Service\Api\Colissimo;
use App\Http\Service\PDF\CreatePdf;
use App\Http\Service\Api\Transfertext;
use App\Http\Service\Api\TransferOrder;
use App\Repository\User\UserRepository;
use Illuminate\Support\Facades\Response;
use App\Repository\Label\LabelRepository;
use App\Repository\Order\OrderRepository;
use App\Http\Service\Api\ColissimoTracking;
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
    private $transfert;
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
    private $colissimoTracking;

    public function __construct(Api $api, UserRepository $user, 
      OrderRepository $order,
      TransferOrder $factorder,
      Transfertext $transfert,
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
      CommandeidsRepository $commandeids,
      ColissimoTracking $colissimoTracking
    ){
      $this->api = $api;
      $this->user = $user;
      $this->order = $order;
      $this->factorder =$factorder;
      $this->transfert = $transfert;
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
      $this->colissimoTracking = $colissimoTracking;
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

        // Gets orders from local orders (Wocoommerce)
        $orders = $this->order->getAllOrdersByUsersNotFinished()->toArray(); 

        // Gets orders from orders_doli (dolibarr and other)
        $orderDolibarr = $this->orderDolibarr->getAllOrdersWithoutProducts();
        
        if(count($orderDolibarr) > 0){ 
          foreach($orderDolibarr as $ord){
            $orderDolibarr = $this->woocommerce->transformArrayOrderDolibarr($ord, $product_to_add_label = null, $withProducts = false);
            $orders[] = $orderDolibarr[0];
          }
        } 
        
        if(count($orders) > 0){

          // List of distributors
          $distributors = $this->distributor->getDistributors();
          $distributors_list = [];
          foreach($distributors as $dis){
            $distributors_list[] = $dis->customer_id;
          }

          foreach($orders as $key => $order){
              if(!isset($order['from_dolibarr'])){
                $orders[$key] = $this->woocommerce->transformArrayOrder([$order], $specific_product = [], $withProducts = false);
                $orders[$key][0]['total'] = $orders[$key][0]['total_order'];
                $orders[$key][0]['total_tax'] = $orders[$key][0]['total_tax_order'];
                $orders[$key][0]['date_created'] = $orders[$key][0]['date'];
                $orders[$key] = $orders[$key][0];
              } 

              // Check if is distributor
              if(in_array($order['customer_id'], $distributors_list)){
                $orders[$key]['is_distributor'] = true;
              } else {
                $orders[$key]['is_distributor'] = false;
              }

              // Check if is dolibarr order
              if(isset($order['from_dolibarr'])){
                $orders[$key]['user_id'] = $orders[$key]['user_id'];
                $orders[$key]['name'] = $orders[$key]['user_id'] ? "" : "Non attribuée";
                $orders[$key]['status'] =  $orders[$key]['status'];
                $orders[$key]['status_text'] = __('status.'.$orders[$key]['status']);
              } else {
                  $orders[$key]['name'] = $orders[$key]['name'] != null ? $orders[$key]['name'] : "Non attribuée";
                  $orders[$key]['status_text'] = __('status.'.$orders[$key]['status']);
              }
              $list_orders[] = $orders[$key];
          }

          return $list_orders;
        } else {
          return array();
        }
      }
    } 


    public function getOrder(){
      return $this->orders(Auth()->user()->id);
    }

    public function getAllOrders(){
      // Preparator
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

    // Assigne all orders between users
    public function distributionOrders(){

      $array_user = [];
      $orders_user = [];
      $orders_id = [];
      $list_preparateur = [];

      // List of orders already assigned
      $orders_user = $this->order->getAllOrdersByUsersNotFinished()->toArray();
      $orderDolibarr = $this->orderDolibarr->getAllOrders();

      // If not orders return message
      if(count($orders_user) == 0 && count($orderDolibarr) == 0){
        echo json_encode(['success' => false, 'message' => 'Il n\'y a pas de commande à attribuer !']);
        return;
      }

      // List of preparator users (role id 2)
      $users =  $this->user->getUsersByRole([2]);

      foreach($users as $user){
        $array_user[$user['user_id']]['total_products'] = 0;
        $array_user[$user['user_id']]['woocommerce']['order_id'] = [];
        $array_user[$user['user_id']]['dolibarr']['order_id'] = [];
        $list_preparateur[] = $user['user_id'];
      }

      if(count($array_user) == 0){
        echo json_encode(['success' => false, 'message' => 'Il n\'y a pas de préparateurs !']);
        return;
      }

      if(count($orderDolibarr) > 0){
        foreach($orderDolibarr as $ord){
          $orderDolibarr = $this->woocommerce->transformArrayOrderDolibarr($ord);
          $orders_user[] = $orderDolibarr[0];
        }
      } 

      foreach($orders_user as $order){
        if($order['user_id'] != 0){
          if(in_array($order['user_id'], $list_preparateur)){
            if($order['status'] == "processing"){
              $array_user[$order['user_id']]['total_products'] += $order['total_products'];  
            }
            $orders_id [] = $order['order_woocommerce_id'];
          }
        }
      }

      // sort array bys total products desc
      usort($orders_user, function($a, $b) {
        return $b['total_products'] - $a['total_products'];
      });


      // Assign all orders by total products
      foreach ($orders_user as $order) {

        // If not alreday assigned
        if($order['user_id'] == 0 || $order['user_id'] == null){
          $min_products_index = null;
          $min_products = PHP_INT_MAX;
          foreach ($array_user as $index => $distribution) {
              if ($distribution['total_products'] < $min_products) {
                  $min_products = $distribution['total_products'];
                  $min_products_index = $index;
              }
          }

          if(!isset($order['from_dolibarr'])){
            $array_user[$min_products_index]['woocommerce']['order_id'][] = $order['order_woocommerce_id'];
          } else {
            if($order['from_dolibarr']){
              $array_user[$min_products_index]['dolibarr']['order_id'][] = $order['order_woocommerce_id'];
            } else {
              $array_user[$min_products_index]['woocommerce']['order_id'][] = $order['order_woocommerce_id'];
            }
          }
         
          $array_user[$min_products_index]['total_products'] += $order['total_products'];
        }
      }

      if(count($array_user) > 0){
        $assignedOrder = $this->order->updateMultipleOrderAttribution($array_user);
        if($assignedOrder == true){
          echo json_encode(['success' => true]);
        } else {
          echo json_encode(['success' => false, 'message' => $assignedOrder]);
        }
       
      } else {
        echo json_encode(['success' => false, 'message' => 'Aucune commande à attribuer']);
      }
    }

    // Unassign all orders
    public function unassignOrders(){
      if($this->orderDolibarr->unassignOrdersDolibarr()){
        $this->order->unassignOrders();
      } else {
        echo json_encode(['success' => false, 'message' => 'Error']);
      }
    }

    public function orderAlreadyPrepared($order_id){
      // NOTIF COMMANDE PREPARE MULTI COMPTE
      $notification_push = [
        'role' => 2,
        'order_id' => $order_id ?? "",
        'type' => 'order_already_prepared',
      ];
      event(New NotificationPusher($notification_push));
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
            $this->orderAlreadyPrepared($order_id);
            $check_if_order_done = true;
          }
        } else if($from_transfers){
          $check_if_order_done = $this->reassort->checkIfDoneTransfersDolibarr($order_id, $barcode_array, $products_quantity, intval($partial));
          $partial = false;
          $check_if_order_done = true;
        } else {
          if($barcode_array != null){
            $check_if_order_done = $this->order->checkIfDone($order_id, $barcode_array, $products_quantity, intval($partial));
          } else if($partial == "1" && $barcode_array == null){
            $this->order->updateOrdersById([$order_id], "waiting_to_validate");
            $this->orderAlreadyPrepared($order_id);
            $check_if_order_done = true;
          }
        }

        if($check_if_order_done && $partial == "1"){
          
          // List of all leader team (role id 4)
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
            $this->orderAlreadyPrepared($order_id);
            echo json_encode(["success" => true]);
            return;
        } else if($picked && $from_transfers){
            $this->reassort->updateStatusTextReassort($order_id ,"prepared-order");
            // $this->orderAlreadyPrepared($order_id);
            echo json_encode(["success" => true]);
            return;
        } else if($picked && !$from_transfers && !$from_dolibarr){
          $this->order->updateOrdersById([$order_id], "prepared-order");
          $this->api->updateOrdersWoocommerce("prepared-order", $order_id);
          $this->orderAlreadyPrepared($order_id);
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
        // Get id commande by ref order
        $orderRef = $this->orderDolibarr->getOrderByRef($order_id)->toArray();
        if(count($orderRef) != 0){
          $order_id = $orderRef[0]['id'];
          $orderReset = $this->orderDolibarr->orderResetDolibarr($order_id);
        } else {
          echo json_encode(["success" => false]);
        }
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
      $status_finished = $status;

      if($order_id && $status){

        // Si pas de user récupéré
        if($user_id == null && ($from_dolibarr == "false" || $from_dolibarr == "0")){
          $status_finished = "lpc_ready_to_ship";
          $order_details = $this->order->getOrderByIdWithoutProducts($order_id);

          if(isset($order_details[0]['shipping_method'])){
            if(str_contains($order_details[0]['shipping_method'], 'chrono')){
              $status_finished = "chronopost-pret";
            }
          }

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

        // status to not update in woocomemrce, only local
        $ignore_status = ['waiting_to_validate', 'waiting_validate', 'partial_prepared_order', 'partial_prepared_order_validate', 'pending'];

        if($from_dolibarr == "false" || $from_dolibarr == "0"){
          if(!in_array($status,  $ignore_status) && !str_contains($order_id, 'SAV')){
            if($status == "finished"){
              $this->api->updateOrdersWoocommerce($status_finished, $order_id);
            } else {
              $this->api->updateOrdersWoocommerce($status, $order_id);
            } 
          }
          echo json_encode(["success" => $this->order->updateOrdersById([$order_id], $status), 'number_order_attributed' => count($number_order_attributed)]);
        } else {
          
          // Get details orders for incrementation or decrementation stock dolibarr
          if(str_contains($order_id, 'BP') || str_contains($order_id, 'GAL')){
            $res = true;
            $data = $this->orderDolibarr->getOrderDetails($order_id);
            if(count($data) > 0){
              $actual_status = $data[0]['status'];

              if($actual_status != "canceled" && $status == "canceled"){
                $res = $this->orderDolibarr->updateStock($data,"incrementation");
              } else if($actual_status == "canceled"){
                $res = $this->orderDolibarr->updateStock($data,"decrementation");
              }

              if(!$res){
                $update = $this->orderDolibarr->updateOneOrderStatus($status, $order_id);
                echo json_encode(["success" => false, 'number_order_attributed' => count($number_order_attributed), 
                'message' => 'Les stocks n\'ont pas pu être incrémenté ou décrémenté']);
              }
            }
          }
         
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
        // Delete order from table commandeId
        $this->commandeids->deleteOrder($order_id);

        // Update all picked products to 0
        $this->productOrder->update(['pick' => 0, 'pick_control' => 0], $order_id);

        // Maybe delete labels later...
        
        echo json_encode(["success" => true]);
      } catch(Exception $e){
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
      }
    }

    public function checkExpedition(Request $request){
      $order_id = explode(',', $request->post('order_id'))[0];
      $order = $this->order->getOrderById($order_id);

      if($order){
        // Check distributor order
        $is_distributor = false; //$this->distributor->getDistributorById($order[0]['customer_id']) != 0 ? true : false;
        echo json_encode(['success' => true, 'transfers'=> false, 'from_dolibarr' => false, 'order' => $order, 'is_distributor' => $is_distributor, 'status' =>  __('status.'.$order[0]['status'])]);
      } else {
        // Check if dolibarr order
        $order = $this->orderDolibarr->getOrdersDolibarrById($order_id)->toArray();
        if(count($order) > 0){
          echo json_encode(['success' => true, 'transfers'=> false, 'from_dolibarr' => true, 'order' => $order, 'is_distributor' => false, 'status' =>  __('status.'.$order[0]['status'])]);
        } else {
          $order = $this->reassort->getReassortByIdWithMissingProduct($order_id);

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
        // if($order[0]['status'] == "finished"){
        //   echo json_encode(["success" => false, "message" => "Cette commande / transfert est déjà emballé(e) !"]);
        //   return;
        // }
      
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
          $this->factorder->TransferOrder($orders);
          // $this->transfert->Transfertext($orders);

          // Insert la commande dans histories
          $data = [
            'order_id' => $order_id,
            'user_id' => Auth()->user()->id,
            'status' => 'finished',
            'poste' => Auth()->user()->poste,
            'created_at' => date('Y-m-d H:i:s'),
            'total_product' => isset($orders[0]['total_product']) ? $orders[0]['total_product'] : null
          ];

          $this->history->save($data);
          
          if($from_dolibarr){
            $this->orderDolibarr->updateOneOrderStatus("finished", $order_id);
          } else {

            // Status différent selon type de commande
            $status_finished = "lpc_ready_to_ship";
            
            if(isset($orders[0]['shipping_method'])){
              if(str_contains($orders[0]['shipping_method'], 'chrono')){
                $status_finished = "chronopost-pret";
              }
            }

            // Check if order distributor
            if(isset($orders[0]['shipping_method_detail'])){
              if(str_contains($orders[0]['shipping_method_detail'], 'Distributeur') && $orders[0]['is_distributor']){
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
      $history_dolibarr = $this->orderDolibarr->getAllHistoryByUser(Auth()->user()->id);
      $history = count($history_dolibarr) > 0 ? array_merge($history, $history_dolibarr) : $history;
      $printer = $this->printer->getPrinterByUser(Auth()->user()->id);

      // Renvoie la vue historique du préparateurs
      return view('preparateur.history', ['history' => $history, 'printer' => $printer[0] ?? false, 'preparateur' => []]);
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

    public function leaderHistory(Request $request){
      $histories = $this->history->getAllHistory($request->all());
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
          $histories_order[$history['order_id']]['kit'] = $history['kit'] ?? false;
          // $histories_order[$history['order_id']]['prepared'] = $history['order_status'];
          $history['status'] == 'prepared' ? $histories_order[$history['order_id']]['user_id_prepared'] = $history['id'] : '';
          $histories_order[$history['order_id']]['prepared'] = $history['status'] == 'prepared' ? $history['name'] : null;
          $histories_order[$history['order_id']]['finished'] = $history['status'] == 'finished' ? $history['name'] : null;
          $histories_order[$history['order_id']]['finished_date'] = $history['status'] == 'finished' ? date('d/m/Y H:i', strtotime($history['created_at'])) : null;
          $histories_order[$history['order_id']]['prepared_date'] = $history['status'] == 'prepared' ? date('d/m/Y H:i', strtotime($history['created_at'])) : null;
        } 
      }

      // Change prepared to order-prepared
      foreach($histories_order as $key => $hist){
        if($hist['status'] == "prepared"){
          $histories_order[$key]['status'] = "prepared-order";
        }
      }

      return view('leader.history', ['histories' => $histories_order, 'list_status' => __('status'), 'parameter' => $request->all()]);
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
              'items_picked' =>  $histo['status'] == "prepared" ? $histo['total_product'] : 0
            ];
          } else {
            $histo['status'] == "prepared" ? array_push($list_histories[$histo['id']]['prepared_order'],$histo['order_id']) : array_push($list_histories[$histo['id']]['finished_order'],$histo['order_id']);
            $list_histories[$histo['id']]['poste'][] = $histo['poste'];
            
            $list_histories[$histo['id']]['prepared_order'] = array_unique($list_histories[$histo['id']]['prepared_order']);
            $list_histories[$histo['id']]['finished_order'] = array_unique($list_histories[$histo['id']]['finished_order']);

            $list_histories[$histo['id']]['poste'] = array_unique($list_histories[$histo['id']]['poste']);

            $list_histories[$histo['id']]['prepared_count'] = count($list_histories[$histo['id']]['prepared_order']);
            $list_histories[$histo['id']]['finished_count'] = count($list_histories[$histo['id']]['finished_order']);
            $histo['status'] == "prepared" ? $list_histories[$histo['id']]['items_picked'] = $list_histories[$histo['id']]['items_picked'] + $histo['total_product'] : '';
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
              'items_picked' =>  $histo['status'] == "prepared" ? $histo['total_product'] : 0
            ];
          } else {
            $histo['status'] == "prepared" ? array_push($list_histories[$histo['id']]['prepared_order'],$histo['order_id']) : array_push($list_histories[$histo['id']]['finished_order'],$histo['order_id']);
            $list_histories[$histo['id']]['poste'][] = $histo['poste'];
            
            $list_histories[$histo['id']]['prepared_order'] = array_unique($list_histories[$histo['id']]['prepared_order']);
            $list_histories[$histo['id']]['finished_order'] = array_unique($list_histories[$histo['id']]['finished_order']);

            $list_histories[$histo['id']]['poste'] = array_unique($list_histories[$histo['id']]['poste']);

            $list_histories[$histo['id']]['prepared_count'] = count($list_histories[$histo['id']]['prepared_order']);
            $list_histories[$histo['id']]['finished_count'] = count($list_histories[$histo['id']]['finished_order']);
            $histo['status'] == "prepared" ? $list_histories[$histo['id']]['items_picked'] = $list_histories[$histo['id']]['items_picked'] + $histo['total_product'] : '';
          }
      }

      return $this->pdf->generateHistoryOrders($list_histories, $date);
    }

    public function leaderHistoryOrder(){
      $history = $this->order->getAllHistory();
      $history_dolibarr = $this->orderDolibarr->getAllHistory();
      $history = count($history_dolibarr) > 0 ? array_merge($history, $history_dolibarr) : $history;
      $printer = $this->printer->getPrinterByUser(Auth()->user()->id);

      $preparateur = [];
      foreach($history as $histo){
        $preparateur[] = $histo['preparateur'];
      }

      $preparateur = array_unique($preparateur);

      // Renvoie la vue historique du préparateurs mais avec toutes les commandes de chaque préparateurs
      return view('preparateur.history', ['history' => $history, 'printer' => $printer[0] ?? false, 'preparateur' => $preparateur]);
    }

    public function deleteOrderProducts(Request $request){
      $order_id = $request->post('order_id');
      $line_item_id = $request->post('line_item_id');
      $increase = $request->post('increase');
      $quantity = $request->post('quantity');
      $product_id = $request->post('product_id');

      try{
        // Supprimer de ma base en local le produit lié à la commande
        $order = $this->order->getOrderById($order_id);
        $order = $this->woocommerce->transformArrayOrder($order);

        $total_tax_order_to_subtract = 0;
        $total_order_to_subtract = 0;

        if($order[0]){
          foreach($order[0]['line_items'] as $value){
            if($value['product_id'] == $product_id){
              $total_tax_order_to_subtract = $value['total_tax'];
              $total_order_to_subtract = floatval($value['total_tax'] + $value['total']);
            }
          }
        }

        $total_tax = $order[0]['total_tax_order'] - $total_tax_order_to_subtract;
        $total = $order[0]['total_order'] - $total_order_to_subtract;
        $order[0]['total_tax_order'] = $total_tax;
        $order[0]['total_order'] = $total;
        $update_order = $this->order->updateTotalOrder($order_id, array("total_tax" => $total_tax, "total" => $total));

        if($this->productOrder->deleteProductOrderByLineItem($order_id, $line_item_id)){
          echo json_encode(['success' => true, 'order' => $order]);
        }
      } catch(Exception $e){
        echo json_encode(['success' => false]);
      }


      //Supprimer de la commande via api woocommerce
      // $delete = $this->api->deleteProductOrderWoocommerce($order_id, $line_item_id, $increase, $quantity, $product_id);

      // // Update le total de la commande en base de données
      // if(is_array($delete)){
      //   $update_order = $this->order->updateTotalOrder($order_id, $delete);
      //   echo json_encode(['success' => true, 'order' => $delete]);
      // } else {
      //   echo json_encode(['success' => false]);
      // }
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

      if(str_contains($order_id, 'CO') || str_contains($order_id, 'BP') || str_contains($order_id, 'GAL')){
        echo json_encode(['success' => false, 'message' => 'Impossible de rajouter des produits pour des commandes qui viennent de Dolibarr !']); 
      } else {
        // Ajout Woocommerce
        $product_order_woocommerce = $this->api->addProductOrderWoocommerce($order_id, $product , $quantity);

        if(is_array($product_order_woocommerce)){
          $update_order = $this->order->updateTotalOrder($order_id, $product_order_woocommerce);
          $insert_product_order = $this->productOrder->insertProductOrder($product_order_woocommerce);
  
          echo json_encode(['success' => $insert_product_order, 'order' => $product_order_woocommerce]); 
        } else {
          echo json_encode(['success' => false]); 
        }
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
          $tabProduit = [];
          $productToIgnore = [];
          $productsToTransfer = [];
          $tabProduitReassort = $this->reassort->findByIdentifiantReassort($identifiant_reassort);

          if($tabProduitReassort){
            if($tabProduitReassort[0]['status'] == "finished"){
              echo json_encode(["success" => false, "message" => "Ce transfert est déjà terminé !"]);
              return;
            }

            if($tabProduitReassort[0]['status'] == "processing"){
              echo json_encode(["success" => false, "message" => "Veuillez terminer la préparation de ce transfert avant de le valider"]);
              return;
            }
            
            // if(isset($tabProduitReassort[0]['status'])){
            //   file_put_contents("transfert_status".$identifiant_reassort.".txt", $tabProduitReassort[0]['status']);
            //   file_put_contents("transfert_details".$identifiant_reassort.".txt", json_encode($tabProduitReassort[0]));
            // }
    
            // For type == 0
            foreach($tabProduitReassort as $tab){
              if($tab['type'] == 0){
                if($tab['qty'] > $tab['missing']){
                  $tab["qty"] = abs($tab['qty']) - $tab['missing'];
                  $tabProduit[] = $tab;
                  $productsToTransfer[$tab['product_id']] = $tab;
                } else {
                  $productToIgnore[] = $tab['product_id'];
                }
              } 
            }

            // For type == 1
            foreach($tabProduitReassort as $tab){
              if($tab['type'] == 1){
                if(isset($productsToTransfer[$tab['product_id']])){
                  $tab["qty"] = -(abs($tab['qty']) - $productsToTransfer[$tab['product_id']]['missing']);
                  $tabProduit[] = $tab;
                }
              }  
            }

            if (count($tabProduit) == 0) {
                echo json_encode(['success' => false, 'message' => "Transfère introuvable ".$identifiant_reassort." ou aucun produit à transférer"]);
                return;
            }
            
            $apiKey = env('KEY_API_DOLIBAR');   
            $apiUrl = env('KEY_API_URL');
            
            // $data_save = array();
            // $incrementation = 0;
            // $decrementation = 0;
            $total_product = 0;
            // $i = 1;
            $ids = [];
            $updateQuery = "UPDATE prepa_hist_reassort SET id_reassort = CASE";

            $error_product= [];
            foreach ($tabProduit as $key => $line) {
                if ($line["qty"] != 0) {   
                    $total_product = $total_product + intval($line["qty"])*1;
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

                    // products to ignore = products out of stock
                    if(!in_array($line["product_id"], $productToIgnore)){
                      // on execute le réassort
                      $stockmovements = $this->api->CallAPI("POST", $apiKey, $apiUrl."stockmovements",json_encode($data));

                      // If is int transfers is succes
                      if(is_int(json_decode($stockmovements))){
                        if ($stockmovements) {
                          $updateQuery .= " WHEN id = ".$line['id']. " THEN ". $stockmovements;
                          $ids[] = $line['id'];
                        
                          // $i++;  
                          // $incrementation++;
                        }
                      } else {
                        $error_product[] = $data;
                      }
                    }
                }
            }

            $updateQuery .= " ELSE -1 END WHERE id IN (".implode(',', $ids).")";
            DB::update($updateQuery);

            // Update status transfers
            $colonnes_values = ['status' => "finished"];
            $this->reassort->update_in_hist_reassort($identifiant_reassort, $colonnes_values);

            // Insert la commande dans histories
            $data = [
              'order_id' => $identifiant_reassort,
              'user_id' => Auth()->user()->id,
              'status' => 'finished',
              'poste' => Auth()->user()->poste,
              'created_at' => date('Y-m-d H:i:s'),
              'total_product' => $total_product ?? null
            ];

            $this->history->save($data);
            echo json_encode(['success' => true, 'message' => 'Transfert '.$identifiant_reassort.' transféré avec succès !']);
          } else {
            echo json_encode(['success' => false, 'message' => "Aucun transfert n'a été trouvé"]);
          }
      } catch (Exception $e) {
          $this->logError->insert(['order_id' => $identifiant_reassort, 'message' => $e->getMessage()]);
          echo json_encode(['success' => false, 'message' => $e->getMessage()]);
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

        if(str_contains($order_id, 'CO') || str_contains($order_id, 'BP') || str_contains($order_id, 'GAL')){
          $this->orderDolibarr->updateCustomerDetail($data, $order_id);
        } else {
          $this->order->update($data, $order_id);
        }
    } else {
      echo json_encode(['success' => false]);
    }

  }

  function updateStockWoocommerce($identifiant_reassort){

    $data = $this->reassort->getQteToTransfer($identifiant_reassort,[4670,4674]);
    
    
    // dd($data);
    // Enregistrez le temps de début
    $datas_updated_succes = array();
    $datas_updated_error = array();

   

    // Récupérer les ids produit de woocommerce
    $ids_woocomerce = $this->product->getProductsByBarcode($data);

   

    if ($ids_woocomerce["response"]) {
      // on fait l'actualisation sur woocommerce
      $datas = $ids_woocomerce["ids_wc_vs_qte"];

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

      // changer le statut de sychronisation (colonne syncro dans la table prepa_hist_reassort)

      // dd($datas_updated_succes);
      
      $responseSyncr =  $this->reassort->updateColonneSyncro($datas_updated_succes, $identifiant_reassort);


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

    if(str_contains($order_id, 'CO') || str_contains($order_id, 'BP') || str_contains($order_id, 'GAL')){
      $order = $this->orderDolibarr->getOrdersDolibarrById($order_id)->toArray();

      // dd($order);
    } else if(strlen($order_id) == 10 && !str_contains($order_id, 'SAV')){
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

  public function syncHistoriesTotalProduct(){
    $orders_id = [];
    $orders = DB::table('orders')->select('order_woocommerce_id')->get();

    foreach($orders as $order){
      $orders_id[] = $order->order_woocommerce_id;
    }

    $line = DB::table('products_order')->select(DB::raw('SUM(prepa_products_order.quantity) as qty'), 'order_id')->whereIn('order_id', $orders_id)->groupBy('order_id')->get();
    $cases = collect($line)->map(function ($item) {
      return sprintf("WHEN %d THEN '%s'", $item->order_id, intval($item->qty));
    })->implode(' ');

    $query = "UPDATE prepa_histories SET total_product = (CASE order_id {$cases} END)";
    DB::statement($query);
    dd("Ok !");
  }

  public function getTrackingStatus(Request $request) {

    $request->validate([
      'order_id' => 'required',
      'tracking_number' => 'required',
      'origin' => 'required',
    ]); 

    $object = new stdClass();
    $object->tracking_number = $request->post('tracking_number');
    $object->order_id = $request->post('order_id');
    $stepChrono = 0;
    $stepColissimo = 0;

    // Tracking status for colissimo / chronopost
    if($request->post('origin') == "chronopost"){
      $found = [];
      $trackingLabelChronopost = array_reverse($this->chronopost->getStatusDetails([$object]));     
      $status_list = $this->chronopost->getStatusCode();
      foreach($trackingLabelChronopost as $tracking){
        $code = str_replace(' ','', $tracking["code"].trim(''));
   
        foreach($status_list as $key => $list){
          if(in_array($code, $list)){
            $found[$key] = $code;
          }
        }
      }

      $stepChrono = count($found) > 0 ? max(array_keys($found)) : 0;
     
    } else if($request->post('origin') == "colissimo"){
      $trackingLabelColissimo = $this->colissimoTracking->getStatus([$object], true);

      if(isset($trackingLabelColissimo["parcel"]["step"])){
        foreach($trackingLabelColissimo["parcel"]["step"] as $key => $tracking){
          if(isset($tracking['labelShort'])){
            if($tracking['status'] == "STEP_STATUS_ACTIVE" || str_contains($tracking['labelShort'], "Vous pouvez retirer votre colis") 
            || str_contains($tracking['labelShort'], "Votre colis est disponible dans votre point de retrait")){
              $stepColissimo = $key;
            }
          } else {
            if($tracking['status'] == "STEP_STATUS_ACTIVE"){
              $stepColissimo = $key;
            }
          }
        }
      }
    }

    return json_encode(['success' => true, 'details' => $request->post('origin') == "colissimo" ? $trackingLabelColissimo : $trackingLabelChronopost, 'stepChrono' => $stepChrono, 'stepColissimo' => $stepColissimo]);
  }


  public function getOrders($token)
  {
    if($token == "BcVTcO9aqWdtP0ZVvujOJXQxjGT9wtRGG3iGZt8ZvwsZ58kMeJAM9TJlUumqb23C"){
      $status = "processing,order-new-distrib,prepared-order"; // Commande en préparation
      $per_page = 100;
      $page = 1;
      $orders = $this->api->getOrdersWoocommerce($status, $per_page, $page);
      
      if(isset($orders['message'])){
        $this->logError->insert(['order_id' => 0, 'message' => $orders['message']]);
        return false;
      }
  
      if(!$orders){
        return array();
      } 
      
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
      
      // Check if already in database
      $ids = [];
      $ordersAlreadyInDatabase = $this->order->getAllOrdersNotFinished()->toArray();
      foreach ($ordersAlreadyInDatabase as $element) {
        if (isset($element['order_woocommerce_id'])) {
            $ids[] = $element['order_woocommerce_id'];
        }
      }
  
      // Check if not already in database
      $listOrdersToCreate = [];
      foreach ($orders as $order) {
        $item_gift_card = 0;
        $item_is_virtual = 0;

        $take_order = true;
  
        // Check if order have not only gift card and not only virtual product
        foreach($order['line_items'] as $or){
          if(str_contains($or['name'], 'Carte Cadeau')){
              $item_gift_card = $item_gift_card + 1;
          }

          if($or['is_virtual'] == "yes"){
            $item_is_virtual = $item_is_virtual + 1;
          }
        }
  
        // Don't take order with only gift card or only virtual product
        if($item_gift_card == count($order['line_items']) || $item_is_virtual == count($order['line_items'])){
          $take_order = false;
        }
  
        // Ne prend pas les commandes de Nice & Marseille
        if(isset($order['shipping_lines'])){
          if(count($order['shipping_lines']) > 0){
            if(str_contains($order['shipping_lines'][0]['method_title'], "Retrait Dans Notre Magasin À Nice")
              || str_contains($order['shipping_lines'][0]['method_title'], "Retrait Dans Notre Magasin À Marseille")){
              $take_order = false;
            }
          } 
        } 

        if(!in_array($order['id'], $ids) && $take_order){
          $listOrdersToCreate[0][] = $order;
        }
      }

      // Liste des distributeurs
      $distributors = $this->distributor->getDistributors();
      $distributors_list = [];
      foreach($distributors as $dis){
        $distributors_list[] = $dis->customer_id;
      }

      if(count($listOrdersToCreate) > 0){
        return $this->order->insertOrdersByUsers($listOrdersToCreate, $distributors_list);
      } else {
        echo json_encode(['success' => false, 'message' => 'Aucune commande à récupérer']);
      }
    } else {
      echo json_encode(['success' => false, 'message' => 'Token invalide !']);
    }
  }

  // Delete order woocommerce or dolibarr with all products
  public function deleteOrder(Request $request){

    try{
      $input = $request->all();
      $this->validate($request, [
          'order_id' => 'required',
      ]);

      if(isset($input['from_history'])){
        if(!$this->history->delete($input['order_id'])){
          echo json_encode(['success' => false]);
        }
      }
  
      $from_dolibarr = $input['from_dolibarr'] ?? null;
  
      if($from_dolibarr == "false" || (!str_contains($input['order_id'], 'CO') && !str_contains($input['order_id'], 'BP') && !str_contains($input['order_id'], 'GAL'))){
        // Delete from woocommerce
        echo json_encode(['success' => $this->order->delete($input['order_id'])]);
      } else {
        // Delete from Dolibarr
        echo json_encode(['success' => $this->orderDolibarr->delete($input['order_id'])]);
      }
    } catch(Exception $e){
      echo json_encode(['success' => false]);
    }
  }

  public function returnOrder(Request $request){
    
    try{
      $request->validate([
        'order_id' => 'required',
        'product_ids'  => 'required',
        'quantity'  => 'required',
        'total_without_tax'  => 'required',
        'total_with_tax'  => 'required'
      ]);

      if(count($request->post('product_ids')) == 0){
        return redirect()->back()->with('error',  "Veuillez sélectionner des produits à renvoyer !");
      }

      // Order details
      $order = $this->order->getOrderByIdWithCustomer($request->post('order_id'));
      $order = $this->woocommerce->transformArrayOrder($order);

      $shipping_method_name = [
        "lpc_sign" => "Colissimo avec signature",
        "lpc_relay" => "Colissimo relais",
        "lpc_expert" => "Colissimo Expert (4 à 6 jours ouvrés )",
        "local_pickup" => "Retrait dans notre magasin à Marseille 13002",
        "chronotoshopdirect" => "Chronopost - Livraison en relais Pickup",
        "chronorelais" => "Livraison express en point relais",
        "chrono13" => "Livraison express avant 13h",
        "advanced_shipping" => "Retrait Distributeur Malpassé"
      ];

      $payment_method_name = [
        "DONS" => "Don",
        "stripe" => "Carte bancaire",
        "payplug" => "Payer par carte bancaire",
        "apple_pay" => "Payer avec Apple Pay",
        "oney_x3_with_fees" => "Payer en 3x par carte bancaire avec Oney",
        "oney_x4_with_fees" => "Payer en 4x par carte bancaire avec Oney",
        "wc-scalapay-payin3" => "Scalapay - Pay in 3",
        "wc-scalapay-payin4" => "Scalapay - Pay in 4",
        "bacs" => "Virement bancaire",
        "gift_card" => "Carte cadeau",
        "american_express" => "Payer avec Amex",
        "bancontact" => "Payer avec Bancontact"
      ];

      // Calculate tax order
      $quantity = $request->post('quantity');
      $product_ids = $request->post('product_ids');
      $total_without_tax = $request->post('total_without_tax');
      $total_with_tax = $request->post('total_with_tax');
      $total_products = 0;

      $total_order_without_tax = 0;
      $total_order_with_tax = 0;

      foreach($request->post('product_ids') as $product_id){
        $total_products = $total_products + $quantity[$product_id];
        $total_order_without_tax = $total_order_without_tax + (floatval($total_without_tax[$product_id]) * $quantity[$product_id]);
        $total_order_with_tax = $total_order_with_tax + (floatval($total_with_tax[$product_id] * $quantity[$product_id]));
      }

      $total_tax_order = $total_order_with_tax - $total_order_without_tax;

      // List of products to return
      $data_product = [];
      $total_weight = 0;
      foreach($order[0]['line_items'] as $key => $item){
        if (in_array($item['product_id'], array_values($product_ids))) {
          $total_weight = floatval($total_weight + $item['weight']);
          $total_product_with_tax = floatval($total_with_tax[$item['product_id']]) * intval($quantity[$item['product_id']]);
          $total_product_without_tax = floatval($total_without_tax[$item['product_id']]) * intval($quantity[$item['product_id']]);
          $total_tax = $total_product_with_tax - $total_product_without_tax;
          $data_product[] = [
            "order_id" => $request->post('order_id').'_SAV',
            "product_woocommerce_id" => $item['product_id'],
            "category" => $item['category'],
            "category_id" => $item['category_id'],
            "quantity" => intval($quantity[$item['product_id']]),
            "cost" => floatval($total_without_tax[$item['product_id']]),
            "subtotal_tax" => $total_tax,
            "total_tax" => $total_tax,
            "total_price" => floatval($total_without_tax[$item['product_id']]) * intval($quantity[$item['product_id']]),
            "pick" => 0,
            "line_item_id" => $request->post('order_id').''.$key.''.time()
          ];
        }
      }

        $calculate_shipping_amount = $this->calculate_shipping_amount($request->post('shipping_method'), floatval($total_weight), floatval($total_order_with_tax));

         // Create new_order
         $data_order = [
          'order_woocommerce_id' => $request->post('order_id').'_SAV',
          'coupons' => '',
          'discount' => 0,
          'discount_amount' => 0,
          'customer_id' => $order[0]['customer_id'],
          'billing_customer_first_name' => $request->post('billing_customer_first_name'),
          'billing_customer_last_name' => $request->post('billing_customer_last_name'),
          'billing_customer_company' => $request->post('billing_customer_company') ?? '',
          'billing_customer_address_1' => $request->post('billing_customer_address_1'),
          'billing_customer_address_2' => $request->post('billing_customer_address_2') ?? '',
          'billing_customer_city' => $request->post('billing_customer_city'),
          'billing_customer_state' => $request->post('billing_customer_state') ?? '',
          'billing_customer_postcode' => $request->post('billing_customer_postcode'),
          'billing_customer_country' => $request->post('billing_customer_country'),
          'billing_customer_email' => $request->post('billing_customer_email'),
          'billing_customer_phone' => $request->post('billing_customer_phone'),
          'shipping_customer_first_name' => $request->post('shipping_customer_first_name'),
          'shipping_customer_last_name' => $request->post('shipping_customer_last_name'),
          'shipping_customer_company' => $request->post('shipping_customer_company') ?? '',
          'shipping_customer_address_1' => $request->post('shipping_customer_address_1'),
          'shipping_customer_address_2' => $request->post('shipping_customer_address_2') ?? '',
          'shipping_customer_city' => $request->post('shipping_customer_city'),
          'shipping_customer_state' => $request->post('shipping_customer_state') ?? '',
          'shipping_customer_postcode' => $request->post('shipping_customer_postcode'),
          'shipping_customer_country' => $request->post('shipping_customer_country'),
          'shipping_customer_phone' => $request->post('shipping_customer_phone') ?? '',
          'date' => date('Y-m-d H:i:s'), // Actual date
          'total_tax_order' => $total_tax_order,
          'total_order' => floatval($total_order_with_tax + $calculate_shipping_amount),
          'total_products' => $total_products,
          'user_id' => 0,
          'status' => 'processing',
          'shipping_method' => $request->post('shipping_method') ?? "lpc_sign",
          'product_code' => null,
          'shipping_method_detail' => $request->post('shipping_method') ? $shipping_method_name[$request->post('shipping_method')] : "Colissimo avec signature",
          'pick_up_location_id' => null,
          'payment_method' => $request->post('payment_method'),
          'payment_method_title' => $payment_method_name[$request->post('payment_method')],
          'gift_card_amount' => 0,
          'shipping_amount' => $calculate_shipping_amount,
        ];


      $insert = $this->order->insertOrderAndProducts($data_order, $data_product);

      if($insert){
          return redirect()->back()->with('success', 'Commande '.$request->post('order_id').' relancée');
      } else {
        return redirect()->back()->with('error',  "Oops, une erreur est survenue !");
      }
    } catch(Exception $e){
      return redirect()->back()->with('error',  "Oops, une erreur est survenue !");
    }
  }

  private function calculate_shipping_amount($shipping_method, $total_weight, $total_order_with_tax){

    if($shipping_method == "chrono13"){
      if($total_weight > 0 && $total_weight < 1){
        return 6;
      }
      if($total_weight > 1 && $total_weight < 2){
        return 6;
      }
      return 4;
    } else if($shipping_method == "lpc_sign"){
      $shippingCost = 0;
      
      if ($total_weight >= 0 && $total_weight <= 6.999 && $total_order_with_tax >= 100) {
          $shippingCost = 0; // Expédition gratuite pour les paniers de plus de 100€ et poids <= 6.999kg
      } elseif ($total_weight >= 7 && $total_weight <= 100.000 && $total_order_with_tax >= 4200) {
          $shippingCost = 0; // Expédition gratuite pour les paniers de plus de 4200€ et poids entre 7kg et 100kg
      } elseif ($total_weight >= 0.00 && $total_weight <= 0.250) {
          $shippingCost = 7; // Coût d'expédition pour les colis jusqu'à 0.25kg
      } elseif ($total_weight > 0.250 && $total_weight <= 0.500) {
          $shippingCost = 7.5; // Coût d'expédition pour les colis entre 0.25kg et 0.5kg
      } elseif ($total_weight > 0.500 && $total_weight <= 0.750) {
          $shippingCost = 8; // Coût d'expédition pour les colis entre 0.5kg et 0.75kg
      }
      return $shippingCost;
    } else if($shipping_method == "lpc_expert"){
        $shippingCost = 0;
  
        if ($total_weight >= 0 && $total_weight <= 15 && $total_order_with_tax >= 150 && $total_order_with_tax <= 2000) {
          $shippingCost = 0; // Expédition gratuite pour certaines conditions de poids et de prix du panier
        } elseif ($total_weight >= 0 && $total_weight <= 0.5 && $total_order_with_tax <= 1000) {
            $shippingCost = 8.5; // Coût d'expédition pour les colis jusqu'à 0.5kg
        } elseif ($total_weight > 0.5 && $total_weight <= 1 && $total_order_with_tax <= 1000) {
            $shippingCost = 10; // Coût d'expédition pour les colis entre 0.5kg et 1kg
        } elseif ($total_weight > 1 && $total_weight <= 2 && $total_order_with_tax <= 1000) {
            $shippingCost = 13.6; // Coût d'expédition pour les colis entre 1kg et 2kg
        } elseif ($total_weight > 2 && $total_weight <= 3 && $total_order_with_tax <= 1000) {
            $shippingCost = 17.2; // Coût d'expédition pour les colis entre 2kg et 3kg
        }
      
        return $shippingCost;
    } else if($shipping_method == "lpc_relay"){
        $shippingCost = 0;

        if ($total_weight >= 0 && $total_weight <= 10 && $total_order_with_tax >= 100 && $total_order_with_tax <= 1000) {
            $shippingCost = 0.0; // Expédition gratuite pour certains critères de poids et de prix du panier
        } elseif ($total_weight >= 0 && $total_weight <= 0.25 && $total_order_with_tax <= 1000) {
            $shippingCost = 5; // Coût d'expédition pour les colis jusqu'à 0.25kg
        } elseif ($total_weight > 0.25 && $total_weight <= 0.50 && $total_order_with_tax <= 1000) {
            $shippingCost = 5.5; // Coût d'expédition pour les colis entre 0.25kg et 0.5kg
        } elseif ($total_weight > 0.50 && $total_weight <= 0.75 && $total_order_with_tax <= 1000) {
            $shippingCost = 6; // Coût d'expédition pour les colis entre 0.5kg et 0.75kg
        } elseif ($total_weight > 0.75 && $total_weight <= 1.00 && $total_order_with_tax <= 1000) {
            $shippingCost = 6.5; // Coût d'expédition pour les colis entre 0.75kg et 1kg
        }

        return $shippingCost;
    } else {
      return 0;
    }
  }
}


