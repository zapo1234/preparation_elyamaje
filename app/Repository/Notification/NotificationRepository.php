<?php

namespace App\Repository\Notification;

use Exception;
use App\Models\Notification;


class NotificationRepository implements NotificationInterface
{

   private $model;

   public function __construct(Notification $model){
      $this->model = $model;
   }

   public function insert($data){
      return $this->model::insert([
         'from_user' => $data['from_user'],
         'to_user' => $data['to_user'],
         'type' => $data['type'],
         'detail' => $data['detail'],
         'order_id' => $data['order_id'],
         'created_at' => date('Y-m-d H:i:s')
      ]);
   }

   public function notificationRead($user){
      return $this->model::where('to_user', $user)->update(['is_read' => 1]);
   }
}























