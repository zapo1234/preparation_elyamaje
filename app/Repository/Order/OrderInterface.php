<?php

namespace App\Repository\Order;

use App\Model\User;

interface OrderInterface
{
   public function getOrderById($order_id);

   public function insertOrdersByUsers($array_user);

   public function getOrdersByUsers();

   public function getAllOrdersByUsersNotFinished();

   public function getUsersWithOrder();

   public function getOrdersByIdUser($id);

   public function updateOrdersById($ids);

   public function checkIfDone($order_id, $barcode_array, $partial = false);

   public function orderReset($order_id);

   public function updateOrderAttribution($from_user, $to_user);

   public function updateOneOrderAttribution($order_id, $user_id);

   public function getHistoryByUser($user_id);
}


