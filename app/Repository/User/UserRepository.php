<?php

namespace App\Repository\User;

use Exception;
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

   public function getUsersAndRoles($withInactive = false){
      $status = $withInactive ? [0, 1] : [1];

      $users = $this->model->select('users.id as user_id', 'name', 'email', 'role_id', 'role', 'poste', 'type', 'active', 'picture', 'identifier')
         ->Leftjoin('user_roles', 'user_roles.user_id', '=', 'users.id')
         ->Leftjoin('roles', 'roles.id', '=', 'user_roles.role_id')
         ->whereIn('users.active', $status)
         ->orderBy('users.id', 'ASC')
         ->get()
         ->toArray();
      $userRole = [];
      $role = [];

      foreach($users as $user){
   
         $role[$user['user_id']][] = $user['role_id'];
         $role['role_name'][$user['user_id']][] = $user['role'];

         if(isset($userRole[$user['user_id']])){
            $userRole[$user['user_id']]['role_id'] = $role[$user['user_id']];
            $userRole[$user['user_id']]['role'] = $role['role_name'][$user['user_id']];

         } else {
            $userRole[$user['user_id']] = $user; 
            $userRole[$user['user_id']]['role_id'] = [$user['role_id']];
            $userRole[$user['user_id']]['role'] = [$user['role']];

         }
      }

      return $userRole;
   }

   public function getUsersByRole($role){

      $users = $this->model->select('users.id as user_id', 'name', 'email', 'role_id', 'role', 'type', 'picture')
         ->join('user_roles', 'user_roles.user_id', '=', 'users.id')
         ->join('roles', 'roles.id', '=', 'user_roles.role_id')
         ->whereIn('user_roles.role_id', $role)
         ->orderBy('users.id', 'ASC')
         ->get()
         ->toArray();

         $userRole = [];
         $role = [];
   
         foreach($users as $user){
   
            $role[$user['user_id']][] = $user['role_id'];
            $role['role_name'][$user['user_id']][] = $user['role'];

            if(isset($userRole[$user['user_id']])){
               $userRole[$user['user_id']]['role_id'] = $role[$user['user_id']];
               $userRole[$user['user_id']]['role'] = $role['role_name'][$user['user_id']];

            } else {
               $userRole[$user['user_id']] = $user; 
               $userRole[$user['user_id']]['role_id'] = [$user['role_id']];
               $userRole[$user['user_id']]['role'] = [$user['role']];

            }
         }
   
         return $userRole;
   }

   public function getUserByEmail($email, $user_id = false){
      if($user_id){
         return $this->model->where('email', $email)->where('id', '!=', $user_id)->count();
      } else {
         return $this->model->where('email', $email)->get();
      }
   }

   public function getUserByEmailOrdIdentifier($email, $identifier, $user_id = false){
      if($user_id){
         return $this->model->where(function($query) use ($email, $identifier) {
            $query->where('email', $email)
                  ->orWhere('identifier', $identifier);
        })
        ->where('id', '!=', $user_id)
        ->count();
      } else {
         return $this->model->where('email', $email)->orWhere('identifier', $identifier)->get();
      }

      return $this->model->where('email', $email)->orWhere('identifier', $identifier)->get();
   }

   public function getUserById($user_id){
      try{
         return $this->model->where('users.id', $user_id)
            ->join('user_roles', 'user_roles.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->get()
            ->groupBy('users.id')
            ->map(function ($items) {
               $user = $items->first();
               $user['roles'] = $items->pluck('role_id')->toArray();
               $user['roles_name'] = $items->pluck('role')->toArray();
               return $user;
            })
            ->values()
            ->toArray();

      } catch(Exception $e){
         return $e->getMessage();
      }
   }

   public function updateRoleByUser($user_id, $role_id){
      
      try{
         $user = DB::table('user_roles')->where('user_id', $user_id)->get();
         $actuel_role = [];

         if($user) {
            $actuel_role = explode(',', $user[0]->role_id);
            
            if(count($actuel_role) > 1){
               $actuel_role[1] = $role_id;
            } else {
               $actuel_role[0] = $role_id;
            }

            // Si le rôle donné est différent de préparateur, alors lui retirer ses commandes attribuées
            if($role_id != 2){
               DB::table('products_order')->join('orders', 'orders.order_woocommerce_id', '=', 'products_order.order_id')->where('orders.user_id', $user_id)->whereIn('status',['processing', 'en-attente-de-pai', 'order-new-distrib'])->delete();
               DB::table('orders')->where('user_id', $user_id)->whereIn('status',['processing', 'en-attente-de-pai', 'order-new-distrib'])->delete();
            } 

            
            DB::table('user_roles')->where('user_id', $user_id)->where('role_id', $actuel_role)->delete();
            DB::table('user_roles')->insert(['user_id' => $user_id, 'role_id' => $role_id]);
            
            return true;
         }
      } catch(Exception $e){
         return $e->getMessage();
      }
   }

   public function createUser($user_name_last_name, $email, $role, $password, $poste, $type, $identifier){
      try {
         $user = $this->model->create([
            'name'=> $user_name_last_name,
            'identifier' => $identifier,
            'email'=> $email,
            'password'=> $password,
            'poste'=> $poste,
            'type' => $type
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

   public function updateUserById($user_id, $user_name_last_name, $email, $role, $poste, $type, $identifier){
      try{
         $delete_order = true;
         $this->model->where('id', $user_id)->update([
            'name'=> $user_name_last_name,
            'email'=> $email,
            'identifier' => $identifier,
            'poste'=> !in_array('3', $role) ? 0 : $poste,
            'type' => $type
         ]);

         DB::table('user_roles')->where('user_id', $user_id)->delete();

         $roles = [];
         foreach($role as $r){
            $roles[] = [
               'user_id' => $user_id,
               'role_id' => $r,
            ];

            if($r == 2){
               $delete_order = false;
            }
         }

         if($delete_order){
            DB::table('products_order')->join('orders', 'orders.order_woocommerce_id', '=', 'products_order.order_id')->where('orders.user_id', $user_id)->whereIn('status',['processing', 'en-attente-de-pai', 'order-new-distrib'])->delete();
            DB::table('orders')->where('user_id', $user_id)->whereIn('status',['processing', 'en-attente-de-pai', 'order-new-distrib'])->delete();
         } 

         DB::table('user_roles')->insert($roles);
         return true;

      } catch(Exception $e){
         return $e->getMessage();
      }
   }

   public function updateUserDetails($user_id, $data){
      try{
         return $this->model->where('id', $user_id)->update($data);
      } catch(Exception $e){
         return $e->getMessage();
      }
   }

   public function updateUserActive($email){
      try{
         $this->model->where('email', $email)->update(['active' => 1]);

         return true;
      } catch(Exception $e){
         return $e->getMessage();
      }
   }

   public function updateUserActiveById($id){
      try{
         $this->model->where('id', $id)->update(['active' => 1]);

         return true;
      } catch(Exception $e){
         return $e->getMessage();
      }
   }

   public function deleteUser($user_id){
      try{
         $this->model->where('id', $user_id)->update(['active' => 0]);
         // Supprime ses rôles
         DB::table('user_roles')->where('user_id', $user_id)->delete();
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

   public function addRole($id, $role_id){
      $roles[] = [
         'user_id' => $id,
         'role_id' => $role_id,
      ];
      try{
         return DB::table('user_roles')->insert($roles);
      } catch(Exception $e){
         return $e->getMessage();
      }
   }

   public function updatePictureById($user_id, $picture){
      try{ 
         $this->model->where('id', $user_id)->update(['picture' => $picture]);
         return true;
      } catch(Exception $e){
         return $e->getMessage();
      }
   }
}























