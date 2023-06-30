<?php

namespace App\Repository\Don;


interface DonInterface
{
     public function inserts($first_name,$last_name,$email,$order_id,$coupons,$total_order,$date_order);

     public function gettiers();
     
}




