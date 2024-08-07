<?php

namespace App\Http\Controllers;
use Exception;
use App\Models\User;
use App\Helper\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Service\Api\Colissimo;
use Illuminate\Support\Facades\Auth;
use App\Repository\Label\LabelRepository;
use Illuminate\Support\Facades\Validator;
use App\Repository\Product\ProductRepository;
use App\Http\Service\Api\Chronopost\Chronopost;
use App\Repository\LogError\LogErrorRepository;

class ApiController extends Controller
{
   private $label;
   private $colissimo;
   private $chronopost;
   private $logError;
   private $product;

   public function __construct(
      LabelRepository $label,  
      Colissimo $colissimo, 
      Chronopost $chronopost,
      LogErrorRepository $logError,
      ProductRepository $product
   )
   {
      $this->label = $label;
      $this->colissimo = $colissimo;
      $this->chronopost = $chronopost;
      $this->logError = $logError;
      $this->product = $product;
   }

   public function login(Request $request){
      $response = (new UserService($request->email, $request->password))->login();
      return response()->json($response);
   }

   public function checkUser(Request $request){
      if($request->user('sanctum')) {
         return response()->json(['success' => true, 'user' => ['name' => $request->user('sanctum')->name, 'email' => $request->user('sanctum')->email,
         'id' => $request->user('sanctum')->id]]);
      } else {
         return response()->json(['success' => false]);
      }
   }

   // Récupère tous les participants dans la table tickera (personnes ayant acheté le billet du gala 2024)
   public function getAllCustomer(){
      try {
         $customers = DB::table('tickera')->get()->toArray();
         return response()->json(['success' => true, 'customers' => $customers]);
      } catch (Exception $e){
         return response()->json(['success' => false, 'message' => $e->getMessage()]);
      }
   }

   public function updateCustomer(Request $request){
      $amount = $request->post("amount");
      $ticketId = $request->post("ticketId");

      if($amount && $ticketId) {
         try { 
            DB::table('tickera')->where('ticket_id', $ticketId)->update(['amount_wheel' => $amount]);
            return response()->json(['success' => true]);
         } catch (Exception $e){
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
         }
      } else {
         return response()->json(['success' => false]);
      }
   }

   public function getLabels(Request $request){
      try{
         $rangeDate = $request->get('rangeDate') ?? 15;
         $labels = $this->label->getAllLabelsByStatusAndDateApi($rangeDate)->toArray();
         return response()->json(['success' => true, 'labels' => $labels]);
      } catch(Exception $e){
         return response()->json(['success' => false, 'message' => $e->getMessage()]);
      }
     
   }

   public function updateLabelsStatus(Request $request){
      // Liste des étiquettes à mettre à jour
      $labels = $request->all();
      $response_coli = true;
      $response_chrono = true;

      try{
         // MISE A JOUR SUR WOOCOMMERCE
         if(count($labels['colissimo']) > 0){
            $response_coli = $this->colissimo->trackingStatusLabel($labels['colissimo']);
         }

         if(count($labels['chronopost']) > 0){
            $response_chrono = $this->chronopost->trackingStatusLabel($labels['chronopost']);
         }

         // MISE A JOUR EN LOCAL
         $local = $this->label->updateLabelStatus($labels);

         // ENREGISTREMENT DES POTENTIELLES ERREURS
         if(!$response_coli){
            $this->logError->insert(['order_id' => 0, 'message' => 'Erreur mise à jour étiquettes colissimo : '.$response_coli]);
         }
         if(!$response_chrono){
            $this->logError->insert(['order_id' => 0, 'message' => 'Erreur mise à jour étiquettes chronopost : '.$response_chrono]);
         }

         return response()->json(['success' => true, 'colissimo' => $response_coli, 'chronopost' => $response_chrono, 'local' => $local]);

      } catch (Exception $e){
         $this->logError->insert(['order_id' => 0, 'message' => 'Mise à jour des status étiquettes : '.$e->getMessage()]);
         return response()->json(['success' => false, 'message' => $e->getMessage()]);

      }
     

   }

   public function productApi(Request $request){
      try{
          
          // recupérer les produits
          $result_data = $this->product->getProduct();
          dd($result_data);

      } catch (Exception $e){
         
           return('error');
      }

   }
}
