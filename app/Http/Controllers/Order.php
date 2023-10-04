<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
use App\Http\Service\Api\Colissimo;
use App\Http\Service\PDF\CreatePdf;
// use Illuminate\Support\Facades\Mail;
use App\Http\Service\Api\TransferOrder;
use App\Repository\User\UserRepository;
use App\Repository\Label\LabelRepository;
use App\Repository\Order\OrderRepository;
use App\Repository\History\HistoryRepository;
use App\Repository\Printer\PrinterRepository;
use App\Repository\Product\ProductRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Http\Service\Api\Chronopost\Chronopost;
use App\Repository\Colissimo\ColissimoRepository;
use App\Http\Service\Woocommerce\WoocommerceService;
use Illuminate\Routing\Controller as BaseController;
use App\Repository\Distributor\DistributorRepository;
use App\Repository\Notification\NotificationRepository;
use App\Repository\ProductOrder\ProductOrderRepository;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Repository\LabelProductOrder\LabelProductOrderRepository;
use App\Repository\LogError\LogErrorRepository;
use App\Repository\OrderDolibarr\OrderDolibarrRepository;
use App\Repository\Reassort\ReassortRepository;

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
    OrderDolibarrRepository $orderDolibarr
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
    }
  
    public function orders($id = null, $distributeur = false){

      if($id){
        $orders_user = $this->order->getOrdersByIdUser($id, $distributeur);

        $orderDolibarr = $this->orderDolibarr->getAllOrdersDolibarrByIdUser($id);
        if(count($orderDolibarr['orders']) > 0){
          if(!$distributeur){
            foreach($orderDolibarr['orders'] as $ord){
              $orders_user['orders'][] = $ord;
            }
          }
          $orders_user['count']['order'] = $orders_user['count']['order'] + count($orderDolibarr['orders']);
        }

        return $orders_user;
      } else {
        $status = "processing,order-new-distrib,prepared-order"; // Commande en préparation
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
                if($order['shipping_lines'][0]['method_title'] == "Retrait dans notre magasin à Nice 06100" 
                  || $order['shipping_lines'][0]['method_title'] == "Retrait dans notre magasin à Marseille 13002"){
                  $take_order = false;
                }
              } 
            }
            
            if($take_order == true){
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
            if(isset($order['shipping_lines'])){
              if(count($order['shipping_lines']) > 0){
                if($order['shipping_lines'][0]['method_title'] != "Retrait dans notre magasin à Nice 06100"
                  && $order['shipping_lines'][0]['method_title'] != "Retrait dans notre magasin à Marseille 13002"){
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
       
        $this->order->insertOrdersByUsers($array_user);
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
              $email = $lead['email'];
              $name = $lead['name'];

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
        }
        echo json_encode(["success" => $check_if_order_done]);
      } else {
        // Check if all products are picked
        $products = $this->productOrder->getProductsByOrderId($order_id);
        $picked = true;
        foreach($products as $p){
          if($p->pick != 1){
            $picked = false;
          }
        }

        if($picked){
          $this->order->updateOrdersById([$order_id], "prepared-order");
          $this->api->updateOrdersWoocommerce("prepared-order", $order_id);
          echo json_encode(["success" => true]);
        }

        echo json_encode(["success" => $picked]);
      }
    }
    

    public function transfersPrepared(Request $request){
      $barcode_array = $request->post('pick_items');
      $products_quantity = $request->post('pick_items_quantity');
      $order_id = $request->post('order_id');

      if($barcode_array != null){
        $check = $this->reassort->checkIfDone($order_id, $barcode_array, $products_quantity);
      }
     
      echo json_encode(["success" => $check]);

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
      $from_dolibarr = $request->post('from_dolibarr');

      if($order_id && $user_id){
        if($from_dolibarr == "false"){
          $update = $this->order->updateOneOrderAttribution($order_id, $user_id);
        } else {
          $update = $this->orderDolibarr->updateOneOrderAttributionDolibarr($order_id, $user_id);
        }

        $number_order_attributed = $this->order->getOrdersByUsers();
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
        if($user_id == null){
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


        if($from_dolibarr == "false"){
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

    public function checkExpedition(Request $request){
      $order_id = $request->get('order_id');
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

      // Sécurité dans le cas ou tout le code barre est envoyé, on récupère que le numéro
      $from_dolibarr = $request->post('from_dolibarr') == "false" ? 0 : 1;
      $transfers = $request->post('transfers') == "false" ? 0 : 1;
      $order_id = explode(',', $request->post('order_id'))[0];

      if($from_dolibarr){
        // Si commande dolibarr je fournis le fk_command
        $order = $this->orderDolibarr->getOrdersDolibarrById($order_id);
      } else if($transfers){
        // Si transfert, envoyé les données à Lyes pour le valider
        $order = $this->reassort->getReassortById($order_id);
      } else {
        $order = $this->order->getOrderByIdWithCustomer($order_id);
      }

      if($order && count($order) > 0){
        if($order[0]['status'] != "prepared-order" && $order[0]['status'] != "processing"){
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
              // Modifie le status de la commande sur Woocommerce en "Prêt à expédier"
              $this->order->updateOrdersById([$order_id], "finished");
              $this->api->updateOrdersWoocommerce("lpc_ready_to_ship", $order_id);
            }
        } catch(Exception $e){
          $this->logError->insert(['order_id' => $order_id, 'message' => $e->getMessage()]);
          echo json_encode(['success' => true, 'message' => 'Commande '.$order[0]['order_woocommerce_id'].' préparée avec succès !']);
        }

        // Génère l'étiquette ou non
        if($request->post('label') == "true"){
          return $this->generateLabel($orders);
        } else {
          echo json_encode(['success' => true, 'message' => 'Commande '.$order[0]['order_woocommerce_id'].' préparée avec succès !']);
        }
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
    private function generateLabel($order){

      $colissimo = $this->colissimoConfiguration->getConfiguration();
      $product_to_add_label = [];
      $quantity_product = [];

      if($order){
        $weight = 0; // Kg

        foreach($order[0]['line_items'] as $or){
          $weight = $weight + number_format(($or['weight'] *$or['quantity']), 2);
          $product_to_add_label[] = $or['product_id'];
          $quantity_product[$or['product_id']] = $or['quantity'];
        } 


        if(str_contains($order[0]['shipping_method'], 'chrono')){
          $labelChrono = $this->chronopost->generateLabelChrono($order[0], $weight, $order[0]['order_woocommerce_id'], count($colissimo) > 0 ? $colissimo[0] : null);
          if(isset($labelChrono['success'])){
              $labelChrono['label'] = mb_convert_encoding($labelChrono['label'], 'ISO-8859-1');
              $insert_label = $this->label->save($labelChrono);
              $insert_product_label_order = $this->labelProductOrder->insert($order[0]['order_woocommerce_id'], $insert_label, $product_to_add_label, $quantity_product);
          } else {
              return redirect()->route('labels')->with('error', $labelChrono);
          }
        } else {
          $label = $this->colissimo->generateLabel($order[0], $weight, $order[0]['order_woocommerce_id'], count($colissimo) > 0 ? $colissimo[0] : null);

          if(isset($label['success'])){
            $label['label'] =  mb_convert_encoding($label['label'], 'ISO-8859-1');
            $label['cn23'] != null ? mb_convert_encoding($label['cn23'], 'ISO-8859-1') : $label['cn23'];
            $insert_label = $this->label->save($label);
            $insert_product_label_order = $this->labelProductOrder->insert($order[0]['order_woocommerce_id'], $insert_label, $product_to_add_label, $quantity_product);
  
            if(is_int($insert_label) && $insert_label != 0 && $insert_product_label_order){
  
              // ----- Print label to printer Datamax -----
              if($label['label_format'] == "ZPL"){
                echo json_encode(['success' => true, 'file' => base64_encode($label['label']), 'message'=> 'Étiquette générée pour la commande '.$order[0]['order_woocommerce_id']]);
              } else if($label['label_format'] == "PDF"){
                return base64_encode($label['label']);
              } else {
                echo json_encode(['success' => true, 'message'=> 'Étiquette générée pour la commande '.$order[0]['order_woocommerce_id']]);
              }
              // ----- Print label to printer Datamax -----
  
            } else {
              echo json_encode(['success' => false, 'message'=> 'Étiquette générée et disponible sur Woocommerce mais erreur base préparation !']);
            }
          } else {
            echo json_encode(['success' => false, 'message'=> 'Commande validée mais erreur génération d\'étiquette : '.$label]);
          }
        }
  
      }
    }

    public function leaderHistory(){
      $histories = $this->history->getAllHistory();
      $histories_order = [];

      foreach($histories as $history){
        if(isset($histories_order[$history['order_id']])){
          if($histories_order[$history['order_id']]['status'] == "prepared"){
            $histories_order[$history['order_id']]['prepared'] = $histories_order[$history['order_id']]['name'];
            $histories_order[$history['order_id']]['finished'] = $history['name'];
            $histories_order[$history['order_id']]['prepared_date'] = date('d/m/Y H:i', strtotime($histories_order[$history['order_id']]['created_at']));
            $histories_order[$history['order_id']]['finished_date'] = date('d/m/Y H:i', strtotime($history['created_at']));
          } else {
            $histories_order[$history['order_id']]['prepared'] = $history['name'];
            $histories_order[$history['order_id']]['finished'] = $histories_order[$history['order_id']]['name'];
            $histories_order[$history['order_id']]['finished_date'] = date('d/m/Y H:i', strtotime($histories_order[$history['order_id']]['created_at']));
            $histories_order[$history['order_id']]['prepared_date'] = date('d/m/Y H:i', strtotime($history['created_at']));
          }
        } else {
          $histories_order[$history['order_id']] = $history;
          $histories_order[$history['order_id']]['prepared'] = $history['status'] == 'prepared' ? $history['name'] : null;
          $histories_order[$history['order_id']]['finished'] = $history['status'] == 'finished' ? $history['name'] : null;
          $histories_order[$history['order_id']]['finished_date'] = $history['status'] == 'finished' ? date('d/m/Y H:i', strtotime($history['created_at'])) : null;
          $histories_order[$history['order_id']]['prepared_date'] = $history['status'] == 'prepared' ? date('d/m/Y H:i', strtotime($history['created_at'])) : null;
        } 
      }

      return view('leader.history', ['histories' => $histories_order]);
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
}


