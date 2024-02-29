<?php

namespace App\Repository\Notification;

use Exception;
use App\Models\NotificationStock;


class NotificationstocksRepository implements NotificationstocksInterface
{

   private $model;

   public function __construct(NotificationStock $model){
      $this->model = $model;
   }

   public function insert($data){
      

   }

}























