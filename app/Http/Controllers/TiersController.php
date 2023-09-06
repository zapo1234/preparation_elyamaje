<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
use App\Repository\Tiers\TiersRepository;
use App\Http\Service\Api\TransferOrder;
use App\Models\History;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

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
           
           
          

        return view('Tiers.orderfacturer',['list_result'=>$list_result]);
    }
    
    
    public function getidscommande(Request $request){
        
         $id = $request->get('id');
          // créeer des intervalle de date pour recupérer le nombre de commande prepare.
         $mm = "09:00:00";
         $mm1 = "23:59:59";
         $date1 = $id.'T'.$mm;
         $date2  = $id.'T'.$mm1;
         $status ="prepared";
          // recupérer les ids de produits dans ce intervale.
          $posts = History::where('status','=',$status)->whereBetween('created_at', [$id, $date2])->get();
           $name_list = json_encode($posts);
            $name_lists = json_decode($posts,true);

            dd($name_lists);
            dump($date1);
            dd($date2);
        
        dd($id);
        
    }

    
  }




