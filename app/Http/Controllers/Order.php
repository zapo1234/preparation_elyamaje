<?php

namespace App\Http\Controllers;
use Exception;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
use App\Http\Service\Api\Colissimo;
use App\Http\Service\PDF\CreatePdf;
use Illuminate\Support\Facades\Mail;
use App\Http\Service\Api\TransferOrder;
use App\Http\Service\Woocommerce\WoocommerceService;
use App\Repository\Distributor\DistributorRepository;
use App\Repository\User\UserRepository;
use Illuminate\Support\Facades\Response;
use App\Repository\Label\LabelRepository;
use App\Repository\Order\OrderRepository;
use App\Repository\History\HistoryRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use App\Repository\Notification\NotificationRepository;
use App\Repository\ProductOrder\ProductOrderRepository;
use Illuminate\Foundation\Validation\ValidatesRequests;
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
    DistributorRepository $distributor
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
    }

    public function orders($id = null, $distributeur = false){

      if($id){
        $orders_user = $this->order->getOrdersByIdUser($id, $distributeur);
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

        // Récupère les commandes attribuée en base s'il y en a 
        $orders_distributed = $this->order->getAllOrdersByUsersNotFinished()->toArray();  
        $ids = array_column($orders_distributed, "order_woocommerce_id");
        $list_orders = [];
       
        if(count($orders_distributed) > 0){
          foreach($orders as $key => $order){
            $take_order = true;

            if(count($order['shipping_lines']) > 0){
              if($order['shipping_lines'][0]['method_title'] == "Retrait dans notre magasin à Nice 06100"){
                $take_order = false;
              }
            } 

            if($take_order == true){
              $clesRecherchees = array_keys($ids,  $order['id']);
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
              $list_orders[] = $orders[$key];
            }
       
          }
        } else {
          foreach($orders as $key => $order){
            if(count($order['shipping_lines']) > 0){
              if($order['shipping_lines'][0]['method_title'] != "Retrait dans notre magasin à Nice 06100"){
                $list_orders[] = $order;
              }
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
      $users =  $this->user->getUsersByRole([2]);
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

      foreach($users as $user){
        $array_user[$user['user_id']] = [];
      }

      if(count($array_user) == 0){
        echo json_encode(['success' => false, 'message' => 'Il n\'y a pas de préparateurs !']);
        return;
      }

      // Liste des commandes déjà réparties entres les utilisateurs
      $orders_user = $this->order->getAllOrdersByUsersNotFinished()->toArray();

      foreach($orders_user as $order){
        $array_user[$order['user_id']][] =  $order;
        $orders_id [] = $order['order_woocommerce_id'];
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

      // Supprime du tableau les commandes à ne pas prendre en compte si déjà attribuées
      foreach($array_user as $key => $array){
        foreach($array as $key2 => $arr){
          if(in_array($arr['order_woocommerce_id'], $orders_to_delete)){
              unset($array_user[$key][$key2]);
          }
        }
      }

      // Modifie le status des commandes qui ne sont plus en cours dans woocommerce
      $this->order->updateOrdersById($orders_to_update);

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

        // Insert orders by users
        $this->order->insertOrdersByUsers($array_user);
      }
    }

    public function ordersPrepared(Request $request){
      $barcode_array = $request->post('pick_items');
      $products_quantity = $request->post('pick_items_quantity');
      $order_id = $request->post('order_id');
      $partial = $request->post('partial');
      $note_partial_order = $request->post('note_partial_order');

      if($barcode_array != "false" && $order_id && $products_quantity != "false"){
        $check_if_order_done = $this->order->checkIfDone($order_id, $barcode_array, $products_quantity, intval($partial));
       
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

                //Envoie d'un email au préparateur pour informer qu'une command en'a pas pu être traitée
                // Mail::send('email.orderwaiting', ['note_partial_order' =>  $note_partial_order, 'name' => $name, 'order_id' => $order_id], function($message) use($email){
                //     $message->to($email);
                //     $message->from('no-reply@elyamaje.com');
                //     $message->subject('Commande incomplète');
                // });
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


    public function updateOrderStatus(Request $request){
      $order_id = $request->post('order_id');
      $status = $request->post('status');
      $user_id = $request->post('user_id');

      if($order_id && $status){

        if($status == "waiting_validate"){
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

        if(!in_array($status,  $ignore_status)){
          if($status == "finished"){
            $this->api->updateOrdersWoocommerce("lpc_ready_to_ship", $order_id);
          } else {
            $this->api->updateOrdersWoocommerce($status, $order_id);
          } 
        }

        echo json_encode(["success" => $this->order->updateOrdersById([$order_id], $status), 'number_order_attributed' => count($number_order_attributed)]);
      } else {
        echo json_encode(["success" => false]);
      }
    }

    public function checkExpedition(Request $request){
      $order_id = $request->get('order_id');
      $order = $this->order->getOrderById($order_id);
      if($order){
        // Check si commande distributeur, si oui rebipper les produits
        $is_distributor = $this->distributor->getDistributorById($order[0]['customer_id']) != 0 ? true : false;
        echo json_encode(['success' => true, 'order' => $order, 'is_distributor' => $is_distributor, 'status' =>  __('status.'.$order[0]['status'])]);
      } else {
        echo json_encode(['success' => false, 'message' => 'Aucune commande ne correspond à ce numéro']);
      }
     
    }

    public function validWrapOrder(Request $request){
          
      $order_id = $request->post('order_id');
      // $order_id = 80283; // Données de test
      $order = $this->order->getOrderByIdWithCustomer($order_id);
      if($order){

        $is_distributor = $order[0]['is_distributor'] != null ? true : false;
  
        if($is_distributor){
          $barcode_array = $request->post('pick_items');
          $products_quantity = $request->post('pick_items_quantity');
          $check_if_order_done = $this->order->checkIfValidDone($order_id, $barcode_array, $products_quantity);

          if(!$check_if_order_done){
            echo json_encode(["success" => false, "message" => "Veuillez vérifier tous les produits !"]);
            return;
          }
        }
        
        $orders = $this->woocommerce->transformArrayOrder($order);
        // envoi des données pour créer des facture via api dolibar....
        $this->factorder->Transferorder($orders);

        // Modifie le status de la commande sur Woocommerce en "Prêt à expédier"
        $this->api->updateOrdersWoocommerce("lpc_ready_to_ship", $order_id);
        $this->order->updateOrdersById([$order_id], "finished");
        
        // Insert la commande dans histories
        $data = [
          'order_id' => $order_id,
          'user_id' => Auth()->user()->id,
          'status' => 'finished',
          'poste' => Auth()->user()->poste,
          'created_at' => date('Y-m-d H:i:s')
        ];
        $this->history->save($data);

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
      return view('preparateur.history', ['history' => $history]);
    }

    // Fonction à appelé après validation d'une commande
    private function generateLabel($order){
     
      $product_to_add_label = [];
      $quantity_product = [];

      if($order){
        $weight = 0; // Kg

        foreach($order[0]['line_items'] as $or){
          $weight = $weight + number_format(($or['weight'] *$or['quantity']), 2);
          $product_to_add_label[] = $or['product_id'];
          $quantity_product[$or['product_id']] = $or['quantity'];
        } 

        $label = $this->colissimo->generateLabel($order[0], $weight, $order[0]['order_woocommerce_id']);

        if(isset($label['success'])){
          $label['label'] =  mb_convert_encoding($label['label'], 'ISO-8859-1');
          $insert_label = $this->label->save($label);
          $insert_product_label_order = $this->labelProductOrder->insert($order[0]['order_woocommerce_id'], $insert_label, $product_to_add_label, $quantity_product);

          if(is_int($insert_label) && $insert_label != 0 && $insert_product_label_order){
            echo json_encode(['success' => true, 'message'=> 'Étiquette générée pour la commande '.$order[0]['order_woocommerce_id']]);
          } else {
            echo json_encode(['success' => false, 'message'=> 'Étiquette générée et disponible sur Woocommerce mais erreur base préparation !']);
          }
        } else {
          echo json_encode(['success' => false, 'message'=> 'Commande validée mais erreur génération d\'étiquette : '.$label]);
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
      // Renvoie la vue historique du préparateurs mais avec toutes les commandes de chaque préparateurs
      return view('preparateur.history', ['history' => $history]);
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
}


