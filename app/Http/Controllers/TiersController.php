<?php

namespace App\Http\Controllers;
use Exception;
use App\Models\History;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
use Illuminate\Support\Facades\DB;
use App\Http\Service\Api\TransferOrder;
use App\Repository\Tiers\TiersRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TiersController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private $tiers;

    public function __construct(
    Api $api,
    TiersRepository $tiers,
    TransferOrder $orders
    
    ){
     $this->tiers = $tiers;
     $this->api = $api;
     $this->orders = $orders;

      
    }


    public function getiers()
    {
      $message="";
      return view('Tiers.refreshtiers',['message'=>$message]);

    }

    public function postiers()
    { 
        // recupérer le traitement des tiers pour les inserts dans la table...
      
        $this->tiers->insertiers();
        dd('zapo');
        $this->orders->Transferorders();
        //$this->tiers->insertiers();// mise à jours des tiers.......
        $message="les clients sont bien mis à jours.";
        return view('refreshtiers',['message'=>$message]);

    }

    public function imports($token)
    {
          
         $token ="iPVP2D4soYuNoYCrRwCtcALoLI9kN6PKrbMyIcTk";
         if($token =="iPVP2D4soYuNoYCrRwCtcALoLI9kN6PKrbMyIcTk"){
           $this->tiers->insertiers();
           
            $message="L'import des clients à été bien éffectué !";
            return $message;

         }

    }
    
    public function getorderfact()
    {
          $data =  DB::table('commandeids')->select('date')->get();
        // transformer les retour objets en tableau
         $list = json_encode($data);
         $lists = json_decode($data,true);
          // compter le nombre de ligne par date.
           $orders_line = DB::table('commandeids')
               ->select('date',DB::raw('COUNT(date) as total'))
                ->groupBy('date')
               ->get();
         
           $details_facture = json_encode($orders_line);
           $details_factures = json_decode($orders_line,true);
           $list_result =[];
           
           foreach($details_factures as $values){
               
               $date = explode('-',$values['date']);
               $line_date = $date[2].'/'.$date['1'].'/'.$date[0];
             
                $list_result[] = [
                    
                    'date' => $line_date,
                    'nombre'=>$values['total'],
                    'dat'=>$values['date'],
                   
                   ];
           }

        return view('tiers.orderfacturer',['list_result'=>$list_result]);
    }
    
    
    public function getidscommande(Request $request){
        
            $date = $request->get('id');
            // date au format francais.
            $dates = explode('-',$date);
            $date_frs = $dates[2].'/'.$dates['1'].'/'.$dates[0];

           // créeer des intervalle de date pour recupérer le nombre de commande prepare.
             $mm = "09:00:00";
             $mm1 = "23:59:59";
             $date1 = $date.'T'.$mm;
            $date2  = $date.'T'.$mm1;
             $status ="finished";
          // recupérer les ids de produits dans ce intervale.
           $posts = History::where('status','=',$status)->whereBetween('created_at', [$date1, $date2])->get();
           $name_list = json_encode($posts);
            $name_lists = json_decode($posts,true);
           // nombre de commande prepared
            $nombre_commande = count($name_lists);

            $list_ids_prepared =[];
            foreach($name_lists as $val){
              
               $list_ids_prepared[] = $val['order_id'];
            }

            // recupérer les facture facturés 
            // recupérer 
            $data =  DB::table('commandeids')->select('id_commande','date')->where('date','=',$date)->get();
            // transformer les retour objets en tableau
            $list = json_encode($data);
            $lists = json_decode($data,true);
            $nombre_facture = count($lists);
           
             $list_ids_commande =[];
             foreach($lists as $values){
                 $list_ids_commande[] = (int)$values['id_commande'];
             }

            // chercher les diff entre les deux tableau. 
             $diff_array = array_diff($list_ids_commande,$list_ids_prepared);

             $list_commande = implode(',',$diff_array);// la liste des ids commande non facturés.
             if(count($diff_array)==0){
                $alert = "Toutes les commandes ont étés facturées le $date_frs";
             }
             elseif(count($diff_array)==1){
              $alert = "Attention nous avons une commande non prise en compte dans les statistiques  le $date_frs voir le N° $list_commande";
            }
            else{
                 $nombre = count($diff_array);
                 $alert="Attention nous avons $nombre commandes non prise en compte dans les statistiques le $date_frs voir les N°  suivant $list_commande";
             }
              
             dump($alert);
             dd('Demande bien excutée');

            echo json_encode(['date_frs' => $date_frs,'alert'=>$alert,'nombre_commande'=>$nombre_commande,'nombre_facture'=>$nombre_facture,'list_commande'=>$list_commande]);
        
    }


    public function csvcreate_product(array $datas)
    {
        $filename = "commande_non_facture.csv";
       $fp = fopen('php://output', 'w');
     // créer une entete du tableau .
     $header = array('date_facturation_preparation','id-commande');
    // gérer les entete du csv 
      header('Content-type: application/csv');
     header('Content-Disposition: attachment; filename=' . $filename);
     fputcsv($fp, $header);
  
      foreach ($datas as $row) {
        fputcsv($fp, $row);
     }
      exit();
   }



    public function getinvoices(Request $request) {

      try{
         $datet = $request->get('id') ?? date('Y-m-d');
         $from_js = $request->get('from_js') ? $request->get('from_js') : false;
         $data = $this->tiers->getinvoices($datet);
   
         $list_result =[];
         $ids_commande = [];
   
         foreach($data as $valu){
            $date = date('Y-m-d', $valu['datem']);
            foreach($valu['array_options'] as $val)
            if($date == $datet){
               if($val!=""){
                  $list[] =(int)$valu['array_options']['options_idw'];
               }
            }
         }
         
         $array_result = array_unique($list);
         $array_finale = array_filter($array_result);
         
         // table historique de facture
         $mm = "07:00:00";
         $mm1 = "23:59:59";
   
         // creé des bornes de recupération dans
         $date1 = $datet.'T'.$mm;
         $date2  = $datet.'T'.$mm1;
         $status ="finished";
   
         // recupérer les ids de produits dans ce intervale.
         $posts = History::where('status','=',"finished")->whereBetween('created_at', ["2023-01-01", "2023-12-31"])->whereRaw('LENGTH(order_id) < 10')->get();
      
         $name_list = json_encode($posts);
         $name_lists = json_decode($posts,true);
   
         $list_array =[];
   
         foreach($name_lists as $value){
            $list_array[] = $value['order_id'];
         }
   
         // resultat la table historique 
         $resultat_histories = $list_array;
         $data = array_unique($list);
   
         // chercher les diff entre les deux tableau. 
         $diff_array = array_diff($resultat_histories,$array_finale);
         $list_commande="";
         if(count($diff_array)){
            $list_commande = implode(',',$diff_array);// la liste des ids commande non facturés.
         }
   
         if(count($diff_array)==0){
            $alert = "Toutes les commandes ont bien été facturées le $datet";
         } elseif(count($diff_array)==1) {
            $alert = "Attention nous avons une commande non facturée dans dolibarr le $datet, voir le N° $list_commande";
         } else {
            $nombre = count($diff_array);
            $alert="Attention nous avons $nombre commandes non facturées dans dolibarr le $datet, voir les N° suivants : $list_commande";
         }
   
         if($from_js == "true" || $from_js == true){
            if(count($diff_array)==0){
               echo json_encode(['success' => true, 'message' => $alert, 'diff' => false]);
               return;
            } else {
               echo json_encode(['success' => true, 'message' => $alert, 'diff' => true]);
               return;
            }
           
         } else {
            dump($alert);
            dd('Demande bien excutée');  
         } 
      } catch (Exception $e){
         echo json_encode(['success' => false, 'message' => 'Oups ! Quelque chose s\'est mal passé']);
         return;
      }
    
   }
}




