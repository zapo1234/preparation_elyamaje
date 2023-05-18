<?php

namespace App\Repository\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Hash;

class UserRepository implements UserInterface

{

   private $model;

   public function __construct(User $model){

      $this->model = $model;
   }


   public function getUsers(){
      return $this->model->all();
   }

   public function getUsersByRole($role){
      return $this->model->select('*')->where('role', $role)->get();
   }
}























