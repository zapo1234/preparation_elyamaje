<?php

namespace App\Repository\Terminal;

use Exception;
use App\Models\Terminal;


class TerminalRepository implements TerminalInterface
{

   private $model;

   public function __construct(Terminal $model){
      $this->model = $model;
   }

   public function getTerminal() {
      return $this->model::all();
   }

   public function insert($data){
      return $this->model::insert($data);
   }

   public function update($terminal_id, $data){
      return $this->model::where('id', $terminal_id)->update($data);
   }

   public function delete($terminal_id){
      return $this->model::where('id', $terminal_id)->delete();
   }
}























