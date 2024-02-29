<?php

namespace App\Repository\Notification;

use Exception;
use App\Models\NotificationStock;
use Illuminate\Support\Facades\DB;


class NotificationstocksRepository implements NotificationstocksInterface
{

   private $model;

   public function __construct(NotificationStock $model){
      $this->model = $model;
   }

   public function insert($data){
       DB::table('notification_stocks')->insert($data);
    }

   public function deletedatable(){
       $qte=1;
       DB::table('notification_stocks')->where('quantite','=',$qte)->delete();
     }

   public function getAll(){
      $qte=1;
      $data =  DB::table('notification_stocks')->select('libelle')->where('quantite','=',$qte)->get();
      $name_list = json_encode($data);
      $name_lists = json_decode($name_list,true);

      return $name_lists;

   }

   public function getAlls(){
      $qte=2;
      $data =  DB::table('notification_stocks')->select('libelle')->where('quantite','=',$qte)->get();
      $name_list = json_encode($data);
      $name_lists = json_decode($name_list,true);

      return $name_lists;

   }


   public function deletedatables(){
      $qte=2;
      DB::table('notification_stocks')->where('quantite','=',$qte)->delete();
     }


  
  
}















































