<?php

namespace App\Repository\Commandeids;

use App\Models\Commandeid;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CommandeidsRepository implements CommandeidsInterface
{
     
    private $ids = [];
    private $model = [];

     public function __construct(Commandeid $model)
     {
        $this->model = $model;
        
     }
     public function getIds(): array
     {
       return $this->ids;
     }
     public function setIds(array $ids)
     {
      $this->ids = $ids;
      return $this;
     }
    
      public function getAll()
      {
           // recupérer tous les data de la table
            $data =  DB::table('commandeids')->select('id_commande')->get();
            $name_list = json_encode($data);
            $name_list = json_decode($data,true);
            $id_commandes =[];
             foreach($name_list as $ks => $val){
               $id_commandes[$val['id_commande']]= $ks;
          }
          // recupérer le tableau des ids commande 
           $this->setIds($id_commandes);
           return $name_list;
        }
    
      public function getAlldate($date){
         // recupérer tous les data de la table
         $data =  DB::table('commandeids')->select('id_commande')->where('date','=',$date)->get();
         $name_list = json_encode($data);
         $name_list = json_decode($data,true);
         return $name_list;
      }

      public function getIdcountry(){
         $data =  DB::table('id_country')->select('rowid','code','label')->get();
         $name_list = json_encode($data);
         $name_list = json_decode($data,true);
         return $name_list;
 
      }
    
     public function deleteOrder($order_id){
      return $this->model::where('id_commande', $order_id)->delete();
     }
    
}