<?php

namespace App\Repository\Don;


interface DonsproductInterface
{
     public function inserts($order_id,$product_id,$label,$qty,$real_price);

     public function getproduct();
     
}




