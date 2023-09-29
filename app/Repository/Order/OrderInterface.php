<?php

namespace App\Repository\Order;

use App\Model\User;

interface OrderInterface
{
   public function getOrderById($order_id);

   public function getOrderByIdWithCustomer($order_id);

   public function getAllOrdersAndLabel();

   public function getAllOrdersAndLabelByFilter($filters);

   public function insertOrdersByUsers($array_user);

   public function getOrdersByUsers();

   public function getAllOrdersByUsersNotFinished();

   public function getUsersWithOrder();

   public function getAllOrdersByIdUser($user_id);

   public function getOrdersByIdUser($id);

   public function updateOrdersById($ids);

   public function updateTotalOrders($data);

   public function checkIfDone($order_id, $barcode_array, $products_quantity, $partial = false);

   public function checkIfValidDone($order_id, $barcode_array, $products_quantity);

   public function orderReset($order_id);

   public function updateOrderAttribution($from_user, $to_user);

   public function updateOneOrderAttribution($order_id, $user_id);

   public function getHistoryByUser($user_id);

   public function getAllHistory();

   public function updateTotalOrder($order_id, $data);

   public function getProductOrder($order_id);

   public function unassignOrders();
}


