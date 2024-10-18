<?php

namespace App\Repository\AccountWoocommerce;
use Illuminate\Support\Facades\DB;

use Exception;


class AccountWoocommerceRepository implements AccountWoocommerceInterface
{


   public function __construct(){
      
   }

   public function getUser($id_wc) {
   // recupérer l'email
     $result = DB::table('account_woocommerce')->select('email')->where('id_user','=',$id_wc)->get();
     return json_decode($result,true);
    
   }


   
}























