<?php

namespace App\Repository\Don;

use Hash;
use Exception;
use App\Models\Don;
use Illuminate\Support\Facades\DB;


class DonRepository implements DonInterface
{

   private $model;

   public function __construct(Don $model){
      $this->model = $model;
   }


   public function inserts($first_name,$last_name,$email,$order_id,$coupons,$total_order,$date_order)
   {
        $tiers = new Don();
        $tiers->first_name = $first_name;
        $tiers->last_name = $last_name;
        $tiers->email = $email;
        $tiers->order_id = $order_id;
        $tiers->coupons = $coupons;
        $tiers->total_order = $total_order;
        $tiers->date_order = $date_order;
        $tiers->save();
       
   }


   public function gettiers()
   {
      $data =  DB::table('dons')->select('email')->get();
      // transformer les retour objets en tableau
      $list = json_encode($data);
      $lists = json_decode($data,true);
      $list_email =[];
      
      foreach($lists as $key =>  $values){
          
          $email_true = mb_strtolower($values['email']);
          $list_email[$email_true] = $key;
      }
        return $list_email;

   }

   
}
   
























