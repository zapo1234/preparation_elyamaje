<?php

namespace App\Repository\Commandeids;

use App\Models\Commandeid;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CommandeidsRepository implements CommandeidsInterface
{
     
    private $ids = [];
    private $model = [];
    private $ficfacture;

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

      public function getIdsinvoices($id_commande){
          $invoices_id= DB::table('fk_factures')->select('id_invoices')->Where('id_commande','=',$id_commande)->get();
           // cree un tableau sous action
            $name_list = json_encode($invoices_id);
             $name_list = json_decode($invoices_id,true);
              $result_id = $name_list[0]['id_invoices'];
             return $result_id;

      }

      public function  getIdsfkfacture(){
         $invoices_id= DB::table('fk_factures')->select('id_invoices','id_commande')->get();
           $name_list = json_encode($invoices_id);
           $name_list = json_decode($invoices_id,true);
           foreach($name_list as $values){
               $result_data[$values['id_invoices']] = $values['id_commande'];
            }

            return $result_data;

      }

      public function createpaiementid(){
            
         // create array _dol
           $data_array =[
             '57,DONS'=>'DONS',
             '107,stripe'=>'stripe',
             '106,payplug'=>'payplug',
             '106,payplug'=>'PAYP',
             '6,CB'=>'CB',
             '6,cod'=>'cod',
             '102,bancontact'=>'bancontact',
             '108,oney_x4_with_fees'=>'oney_x4_with_fees',
             '108,oney_x3_with_fees'=>'oney_x3_with_fees',
             '3,bacs'=>'bacs',
             '57,gift_card'=>'gift_card',
             '6,apple_pay'=>'apple_pay',
             '6,wc-scalapay-payin3' => 'wc-scalapay-payin3',
             '6,wc-scalapay-payin4' => 'wc-scalapay-payin4'
           ];

           return $data_array;
      }


   public function getrowidfacture(){

      $data = DB::connection('mysql2')->select("SELECT rowid,ref FROM llxyq_paiement");
      $name_list = json_encode($data);
      $name_list = json_decode($name_list,true);
      foreach($name_list as $val){
         $data_result[$val['rowid']] = $val['ref'];
      }

      return $data_result;

   }


    
     public function deleteOrder($order_id){
      return $this->model::where('id_commande', $order_id)->delete();
     }
    
}