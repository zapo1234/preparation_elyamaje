<?php

namespace App\Repository\Colissimo;

use App\Models\Colissimo;

class ColissimoRepository implements ColissimoInterface
{
  private $model;

  public function __construct(Colissimo $model){
    $this->model = $model;
  }  

  public function getConfiguration(){
    return $this->model::all();
  }

  public function save($data){
    if($this->model->count() == 0){
      return $this->model::insert($data);
    } else {
      return $this->model::where('id', '>', 0)->update($data);
    }
  }
}