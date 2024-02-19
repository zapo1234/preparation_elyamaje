<?php

namespace App\Repository\ProductOrder;


interface ProductOrderInterface
{
    public function getAllProductsPicked();

    public function deleteProductOrderByLineItem($line_item_id, $order_id);

    public function insertProductOrder($product_order_woocommerce);

    public function getProductsByOrderId($order_id);

    public function getproductdolibar();// recupérer les products via dolibar..

    public function update($data, $order_id);// recupérer les products via dolibar..
}




