<?php

namespace App\Repository\User;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserRepository implements UserInterface

{

   private $model;

   public function __construct(User $model){

      $this->model = $model;
   }


   public function getUsers(){
      return $this->model->all();
   }

   public function getUsersAndRoles(){
      $users = $this->model->select('users.id as user_id', 'name', 'email', 'role_id', 'role')
         ->join('user_roles', 'user_roles.user_id', '=', 'users.id')
         ->join('roles', 'roles.id', '=', 'user_roles.role_id')
         ->orderBy('users.id', 'ASC')
         ->get()
         ->toArray();
      
      $userRole = [];
      $role = [];

      foreach($users as $user){

         $role[$user['user_id']][] = $user['role'];

         if(isset($userRole[$user['user_id']])){
            $userRole[$user['user_id']]['role'] = $role[$user['user_id']];
         } else {
            $userRole[$user['user_id']] = $user; 
            $userRole[$user['user_id']]['role'] = [$user['role']];
         }
      }

      return $userRole;
   }

   public function getUsersByRole($role){

      $users = $this->model->select('users.id as user_id', 'name', 'email', 'role_id', 'role')
         ->join('user_roles', 'user_roles.user_id', '=', 'users.id')
         ->join('roles', 'roles.id', '=', 'user_roles.role_id')
         ->whereIn('user_roles.role_id', $role)
         ->orderBy('users.id', 'ASC')
         ->get();

      return $users;
   }

   public function updateRoleByUser($user_id, $role_id){
      
      $user = $this->model->where('id', $user_id)->get();

      if($user) {
         $actuel_role = explode(',', $user[0]->role_id);
         
         if(count($actuel_role) > 1){
            $actuel_role[1] = $role_id;
         } else {
            $actuel_role[0] = $role_id;
         }
        
         // Si le rôle donné est différent de préparateur, alors lui retirer ses commandes attribuées
         if($role_id != 2){
            DB::table('products')->join('orders', 'orders.order_woocommerce_id', '=', 'products.order_id')->where('orders.user_id', $user_id)->where("orders.status","processing")->delete();
            DB::table('orders')->where('user_id', $user_id)->where('status','processing')->delete();
         } 
      
         return $this->model->where('id', $user_id)->update(['role_id' => implode(',', $actuel_role)]);
      }
   }


   public function createUser($user_name_last_name, $email, $role, $password){

      try{
         $user = $this->model->create([
            'name'=> $user_name_last_name,
            'email'=> $email,
            'password'=> $password,
         ]);

         $roles = [];
         foreach($role as $r){
            $roles[] = [
               'user_id' => $user->id,
               'role_id' => $r,

            ];
         }

         DB::table('user_roles')->insert($roles);
         return true;

      } catch(Exception $e){
         return $e->getMessage();
      }

    
   }
}























