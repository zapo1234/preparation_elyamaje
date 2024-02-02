<?php

namespace App\Repository\OrderDolibarr;


interface OrderDolibarrInterface
{
    public function getAllOrders();

    public function getOrdersDolibarrById($order_id);

    public function updateOneOrderAttributionDolibarr($order_id, $user_id);

    public function updateOrderAttributionDolibarr($from_user, $to_user);

    public function getUsersWithOrderDolibarr();

    public function updateOneOrderStatus($status, $order_id);

    public function unassignOrdersDolibarr();

    public function getAllOrdersDolibarrByIdUser($user_id);

    public function checkIfDoneOrderDolibarr($order_id, $barcode_array, $products_quantity, $partial);

    public function getProductOrder($order_id);

    public function getAllOrdersAndLabelByFilter($filters);

    public function getAllOrdersAndLabel();

    public function orderResetDolibarr($order_id);

    public function getAllProductsPickedDolibarr();

    public function  updateProductOrder($order_id, $product_id, $data);

    public function  deleteProductOrder($order_id, $product_id);

    public function updateCustomerDetail($data, $order_id);

    public function getOrdersBeautyProf($date);

    public function getAllOrdersBeautyProf($user_id, $filters);

    public function getAllOrdersBeautyProfHistory($filters);
}




