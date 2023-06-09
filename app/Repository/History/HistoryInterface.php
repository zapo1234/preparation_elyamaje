<?php

namespace App\Repository\History;


interface HistoryInterface
{
   public function getHistoryByDate($date);

   public function getAllHistory();

   public function save($data);
}


