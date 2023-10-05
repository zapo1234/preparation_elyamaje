<?php

namespace App\Repository\Printer;

use Exception;
use Carbon\Carbon;
use App\Models\Printers;
use Illuminate\Support\Facades\DB;

class PrinterRepository implements PrinterInterface

{

   private $model;

   public function __construct(Printers $model){
      $this->model = $model;
   }

   public function getPrinters(){
      return $this->model->select('printers.*', 'users.name as userName')
      ->leftJoin('users', 'users.id', '=', 'printers.user_id')
      ->get();
   }

   public function addPrinter($data){
      return $this->model->insert($data);
   }

   public function updatePrinter($data, $printer_id){
      return $this->model->where('id', $printer_id)->update($data);
   }

   public function deletePrinter($printer_id){
      return $this->model->where('id', $printer_id)->delete();
   }

   public function getPrinterByUser($user_id){
      return $this->model->where('user_id', $user_id)->get();
   }

   public function updatePrinterAttributionByUser($from_user, $to_user){
      return $this->model->where('user_id', $from_user)->update(['user_id' => $to_user]);
   }
}























