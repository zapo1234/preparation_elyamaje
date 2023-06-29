<?php

namespace App\Repository\Don;

use Hash;
use Exception;
use App\Models\Don;


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
        $tiers->last_name = $lastname;
        $tiers->email = $email;
        $tiers->order_id = $order_id;
        $tiers->coupons = $coupons;
        $tiers->total_order = $total_order;
        $tiers->date_order = $date_order;
        $tiers->save();
       
   }

   
}
   
























