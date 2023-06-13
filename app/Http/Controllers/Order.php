<?php

namespace App\Http\Controllers;
use Exception;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
use App\Http\Service\Api\Colissimo;
use App\Http\Service\PDF\CreatePdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Http\Service\Api\TransferOrder;
use App\Repository\User\UserRepository;
use Illuminate\Support\Facades\Response;
use App\Repository\Label\LabelRepository;
use App\Repository\Order\OrderRepository;
use App\Repository\History\HistoryRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use App\Repository\ProductOrder\ProductOrderRepository;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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
    private $product;


    public function __construct(Api $api, UserRepository $user, 
    OrderRepository $order,
    TransferOrder $factorder,
    HistoryRepository $history,
    CreatePdf $pdf,
    Colissimo $colissimo,
    LabelRepository $label,
    ProductOrderRepository $product
    ){
      $this->api = $api;
      $this->user = $user;
      $this->order = $order;
      $this->factorder =$factorder;
      $this->history = $history;
      $this->pdf = $pdf;
      $this->colissimo = $colissimo;
      $this->label = $label;
      $this->product = $product;
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
        $orders_distributed = $this->order->getAllOrdersByUsersNotFinished()->toArray();  
      
        $ids = array_column($orders_distributed, "order_woocommerce_id");

        if(count($orders_distributed) > 0){
          foreach($orders as $key => $order){
            $clesRecherchees = array_keys($ids,  $order['id']);
            if(count($clesRecherchees) > 0){
              $orders[$key]['user_id'] =  $orders_distributed[$clesRecherchees[0]]['user_id'];
              $orders[$key]['name'] =  $orders_distributed[$clesRecherchees[0]]['name'];
              $orders[$key]['status'] =  $orders_distributed[$clesRecherchees[0]]['status'];
              $orders[$key]['status_text'] = __('status.'.$orders_distributed[$clesRecherchees[0]]['status']);
            } else {
              $orders[$key]['user_id'] = null;
              $orders[$key]['name'] = "Non attribuée";
              $orders[$key]['status'] =  $orders_distributed[$clesRecherchees[0]]['status'];
              $orders[$key]['status_text'] = __('status.'.$orders_distributed[$clesRecherchees[0]]['status']);
            }
  
          }
        }
     
        return $orders;
      }
      
    }
 
    public function getOrder(){
      return $this->orders(Auth()->user()->id);
    }

    public function getAllOrders(){
      // Préparateur
      $users =  $this->user->getUsersByRole([2]);
      $products_pick =  $this->product->getAllProductsPicked()->toArray();
      echo json_encode(['orders' => $this->orders(), 'users' => $users, 'products_pick' => $products_pick]);
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
      $order_id = $request->post('order_id');
      $partial = $request->post('partial');

      if($barcode_array && $order_id ){
        $check_if_order_done = $this->order->checkIfDone($order_id, $barcode_array, $partial);
        if($check_if_order_done && $partial){

            // Récupère les chefs d'équipes
            $leader = $this->user->getUsersByRole([4]);
            foreach($leader as $lead){
                $email = $lead['email'];
                $name = $lead['name'];

                //Envoie d'un email au préparateur pour informer qu'une command en'a pas pu être traitée
                Mail::send('email.orderwaiting', ['name' => $name, 'order_id' => $order_id], function($message) use($email){
                    $message->to('adrien@elyamaje.com');
                    $message->from('no-reply@elyamaje.com');
                    $message->subject('Commande incomplète');
                });
            }
        }
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


    public function updateOrderStatus(Request $request){
      $order_id = $request->post('order_id');
      $status = $request->post('status');

      if($order_id && $status){
        $number_order_attributed = $this->order->getOrdersByUsers();
        echo json_encode(["success" => $this->order->updateOrdersById([$order_id], $status), 'number_order_attributed' => count($number_order_attributed)]);
      } else {
        echo json_encode(["success" => false]);
      }
    }


    public function validWrapOrder(Request $request){
           
        // $order_id = $request->post('order_id');
        $order_id = 64826; // Données de test
        $order = $this->order->getOrderById($order_id);
        if($order){

            $order_new_array = [];
            $products = [];

            $order[0]['order_id'] = $order[0]['order_woocommerce_id'];
            $billing = [
              "first_name" => $order[0]['billing_customer_first_name'],
              "last_name" => $order[0]['billing_customer_last_name'],
              "company" => $order[0]['billing_customer_company'],
              "address_1" => $order[0]['billing_customer_address_1'],
              "address_2" => $order[0]['billing_customer_address_2'],
              "city" => $order[0]['billing_customer_city'],
              "state" => $order[0]['billing_customer_state'],
              "postcode" => $order[0]['billing_customer_postcode'],
              "country" => $order[0]['billing_customer_country'],
              "email" => $order[0]['billing_customer_email'],
              "phone" =>  $order[0]['billing_customer_phone'],
            ];

            $shipping = [
              "first_name" => $order[0]['shipping_customer_first_name'],
              "last_name" => $order[0]['shipping_customer_last_name'],
              "company" => $order[0]['shipping_customer_company'],
              "address_1" => $order[0]['shipping_customer_address_1'],
              "address_2" => $order[0]['shipping_customer_address_2'],
              "city" => $order[0]['shipping_customer_city'],
              "state" => $order[0]['shipping_customer_state'],
              "postcode" => $order[0]['shipping_customer_postcode'],
              "country" => $order[0]['shipping_customer_country'],
              "phone" =>  $order[0]['shipping_customer_phone'],
            ];


       
            // Construis le tableau de la même manière que woocommerce
            foreach($order as $key => $or){
              $products['line_items'][] = ['name' => $or['name'], 'product_id' => $or['product_woocommerce_id'], 'variation_id' => $or['variation'] == 1 ? $or['product_woocommerce_id'] : 0, 
              'quantity' => $or['quantity'], 'subtotal' => $or['cost'], 'total' => $or['total_price'],  'subtotal_tax' => $or['subtotal_tax'],  'total_tax' => $or['total_tax'],
              'weight' =>  $or['weight'], 'meta_data' => [['key' => 'barcode', "value" => $or['barcode']]]];

           
              if($or['total_price'] == 0){
                $products['line_items'][0]['real_price'] = $or['price'];
              }

              foreach($or as $key2 => $or2){
                if (str_contains($key2, 'billing')) {
                  unset($order[$key][$key2]);
                }

                if (str_contains($key2, 'shipping') && !str_contains($key2, 'method')) {
                  unset($order[$key][$key2]);
                }
              }

            }

            $order_new_array =  $order[0];
            $order_new_array['line_items'] = $products['line_items'];
            $order_new_array['billing'] = $billing;
            $order_new_array['shipping'] = $shipping;
         
            // recupérer les function d'ecriture  et création de client et facture dans dolibar.
            $orders[] = $order_new_array;

            // envoi des données pour créer des facture via api dolibar....
             $this->factorder->Transferorder($orders);
            // Modifie le status de la commande sur Woocommerce en "Prêt à expédier"
            // $this->api->updateOrdersWoocommerce("lpc_ready_to_ship", $order_id);
            // $this->order->updateOrdersById([$order_id], "finished");
            // Insert la commande dans histories
            // $data = [
            //   'order_id' => $order_id,
            //   'user_id' => Auth()->user()->id,
            //   'status' => 'finished',
            //   'poste' => Auth()->user()->poste
            // ];
            // $this->history->save($data);
            // Génération de l'étiquette colissimo
            // return $this->generateLabel($orders);
        } else {
            return redirect()->back()->with('error','Aucune commande associée, vérifiez l\'id de la commande !');
        }
    }

    // Historique commande préparateur
    public function ordersHistory(){
      $history = $this->order->getHistoryByUser(Auth()->user()->id);
      return view('preparateur.history', ['history' => $history]);
    }


    // Fonction à appelé après validation d'une commande
    private function generateLabel($order){

      if($order){
        $weight = 0; // Kg

        foreach($order[0]['line_items'] as $or){

          $weight = $weight + ($or['weight'] *$or['quantity']);
        } 
        
        $label = $this->colissimo->generateLabel($order[0], $weight, $order[0]['order_woocommerce_id']);
        // $label['label'] = file_get_contents('labelPDF.pdf');
    
        if(isset($label['success'])){
          $label['label'] =  mb_convert_encoding($label['label'], 'ISO-8859-1');
          if($this->label->save($label)){
            $headers = [
              'Content-Type' => 'application/pdf',
            ];
            return Response::make($label['label'] , 200, $headers);
          }
        } else {
          return redirect()->back()->with('error', $label);
        }
      }
    }

    public function leaderHistory(){
      $histories = $this->history->getAllHistory();
      $histories_by_date = [];

      // Groupe par date
      foreach($histories as $history){
        $histories_by_date[date("Y-m-d", strtotime($history['created_at']))][] = $history;
      }

      return view('leader.history', ['histories_by_date' => $histories_by_date]);
    }

    public function downloadPDF(Request $request){
      $date = $request->post('date_historique');
      $histories = $this->history->getHistoryByDate($date);
      
      // Générer mon pdf
      $this->pdf->generateHistoryOrders($histories, $date);
      return redirect()->back();
    }
}


