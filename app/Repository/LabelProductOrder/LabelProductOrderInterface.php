<?php

namespace App\Repository\LabelProductOrder;


interface LabelProductOrderInterface
{
     public function insert($order_id, $insert_label, $product_to_add_label, $quantity_product);

     public function deleteLabelProductOrderById($label_id);

     public function getLabelProductOrder($order_id);
}




