<?php

namespace App\Repository\Role;

use App\Models\Role;

class RoleRepository implements RoleInterface 
{
   private $model;

   public function __construct(Role $model){
      $this->model = $model;
   }

   public function getRoles(){
      return $this->model->all();
   }

   public function createRole($role){
      return $this->model->insert([
         'role' => $role['role'],
         'color' => $role['color'],
     ]);
   }

   public function updateRole($role){
      return $this->model->where('id', $role['role_id'])->update([
         'role' => $role['role'],
         'color' => $role['color'],
     ]);
   }

   public function deleteRole($role_id){
      return $this->model->where('id', $role_id)->delete();
   }
}























