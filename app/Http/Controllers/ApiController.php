<?php

namespace App\Http\Controllers;
use Exception;
use App\Helper\UserService;
use Illuminate\Http\Request;
use App\Http\Service\Api\Colissimo;
use App\Repository\Label\LabelRepository;
use App\Http\Service\Api\Chronopost\Chronopost;
use App\Repository\LogError\LogErrorRepository;

class ApiController extends Controller
{
   private $label;
   private $colissimo;
   private $chronopost;
   private $logError;

   public function __construct(
      LabelRepository $label,  
      Colissimo $colissimo, 
      Chronopost $chronopost,
      LogErrorRepository $logError
   )
   {
      $this->label = $label;
      $this->colissimo = $colissimo;
      $this->chronopost = $chronopost;
      $this->logError = $logError;
   }

   public function login(Request $request){
      $response = (new UserService($request->email, $request->password))->login();
      return response()->json($response);
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
}
