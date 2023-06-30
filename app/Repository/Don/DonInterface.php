<?php

namespace App\Repository\Don;


interface DonInterface
{
     public function inserts($order_id,$product_id,$label,$qty,$real_price);

     public function gettiers();
     
}




