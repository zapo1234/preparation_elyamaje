<?php

namespace App\Repository\History;


interface HistoryInterface
{
   public function getHistoryByDate($date);

   public function getAllHistory($request);

   public function getHistoryAdmin($date);
   
   public function save($data);
}


