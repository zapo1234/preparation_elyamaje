<?php

namespace App\Repository\LogError;

use Exception;
use App\Models\LogError;


class LogErrorRepository implements LogErrorInterface
{

   private $model;

   public function __construct(LogError $model){
      $this->model = $model;
   }

   public function insert($data){
      return $this->model::insert([
         'order_id' => $data['order_id'],
         'message' => $data['message'],
      ]);
   }

   public function getAllLogs(){
      return $this->model->orderBy('id', 'DESC')->get();
   }

}























