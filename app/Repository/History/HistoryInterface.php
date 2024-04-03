<?php

namespace App\Repository\History;


interface HistoryInterface
{
   public function getHistoryByDate($date);

   public function getAllHistory($request);

   public function getHistoryAdmin($date);

   public function getHistoryByIdUser($user_id);
   
   public function save($data);

   public function delete($order_id);
}


