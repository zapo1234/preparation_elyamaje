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

   public function insert($data)
   {
      return $this->model::insert([
         'first_name' => $data['first_name'],
         'last_name' => $data['last_name'],
         'email' => $data['email'],
         'order_id' => $data['order_id'],
         'coupons' => $data['coupons'],
         'total_order' => $data['total_order'],
         'date_order' => $data['date_order'],
         'created_at'=> date('Y-m-d H:i:s'),
         'updated_at' => date('Y-m-d H:i:s')
      ]);
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
   
























