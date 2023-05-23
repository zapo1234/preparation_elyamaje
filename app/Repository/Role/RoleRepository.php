<?php

namespace App\Repository\Role;

use Hash;
use Carbon\Carbon;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class RoleRepository implements RoleInterface

{

   private $model;

   public function __construct(Role $model){
      $this->model = $model;
   }


   public function getRoles(){
      return $this->model->all();
   }

}























