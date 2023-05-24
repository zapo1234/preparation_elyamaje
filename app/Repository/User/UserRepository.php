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
      return $this->model->select('users.id as user_id', 'name', 'email', 'role_id', 'role')->whereIn('role_id', $role)
      ->join('roles', 'users.role_id', '=', 'roles.id')
      ->orderBy('users.id', 'ASC')
      ->get();
   }

   public function updateRoleByUser($user_id, $role_id){

      // Si le rôle donné est différent de préparateur, alors lui retirer ses commandes attribuées
      if($role_id != 2){
         DB::table('products')->join('orders', 'orders.order_woocommerce_id', '=', 'products.order_id')->where('orders.user_id', $user_id)->where("orders.status","processing")->delete();
         DB::table('orders')->where('user_id', $user_id)->where('status','processing')->delete();
      } 
      
      return $this->model->where('id', $user_id)->update(['role_id' => $role_id]);
   }
}























