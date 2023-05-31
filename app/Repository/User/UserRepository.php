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

   public function getUserByEmail($email, $user_id = false){
      if($user_id){
         return $this->model->where('email',$email)->where('id', '!=', $user_id)->count();
      } else {
         return $this->model->where('email', $email)->count();
      }
   }


   public function getUserById($user_id){
      try{
         return $this->model->where('users.id', $user_id)
            ->join('user_roles', 'user_roles.user_id', '=', 'users.id')
            ->get()
            ->groupBy('users.id')
            ->map(function ($items) {
               $user = $items->first();
               $user['roles'] = $items->pluck('role_id')->toArray();
               return $user;
            })
            ->values()
            ->toArray();

      } catch(Exception $e){
         return $e->getMessage();
      }
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

   public function updateUserById($user_id, $user_name_last_name, $email, $role){

      try{
         
         $this->model->where('id', $user_id)->update([
            'name'=> $user_name_last_name,
            'email'=> $email,
         ]);

         DB::table('user_roles')->where('user_id', $user_id)->delete();

         $roles = [];
         foreach($role as $r){
            $roles[] = [
               'user_id' => $user_id,
               'role_id' => $r,

            ];
         }

         DB::table('user_roles')->insert($roles);
         return true;

      } catch(Exception $e){
         return $e->getMessage();
      }
      // return $this->model->where('id', $user_id)->update(['role_id' => implode(',', $actuel_role)]);
   }

   public function deleteUser($user_id){
      try{
         $this->model->where('id', $user_id)->delete();

         return true;
      } catch(Exception $e){
         return $e->getMessage();
      }
   }

   public function insertToken($email, $token){
      return $this->model->where('email', $email)->update(['remember_token' => $token]);
   }

   public function getUserByToken($token){
      return $this->model->where('remember_token', $token)->count();
   }

   public function updatePassword($token, $password_hash){
      try{ 
         $this->model->where('remember_token', $token)->update(['password' => $password_hash, 'remember_token' => null]);
         return true;
      } catch(Exception $e){
         return $e->getMessage();
      }
   }
}























