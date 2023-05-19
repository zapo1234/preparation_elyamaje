<?php

namespace App\Repository\Order;

use App\Model\User;

interface OrderInterface
{
   public function insertOrdersByUsers($array_user);

   public function getOrdersByUsers();

   public function getOrdersByIdUser($id);

   public function updateOrdersById($ids);
}


