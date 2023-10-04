<?php

namespace App\Repository\OrderDolibarr;


interface OrderDolibarrInterface
{
    public function getAllOrders();

    public function getOrdersDolibarrById($order_id);

    public function updateOneOrderAttributionDolibarr($order_id, $user_id);

    public function updateOneOrderStatus($status, $order_id);

    public function unassignOrdersDolibarr();

    public function getAllOrdersDolibarrByIdUser($user_id);

    public function checkIfDoneOrderDolibarr($order_id, $barcode_array, $products_quantity, $partial);
}




