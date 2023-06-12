<?php

namespace App\Repository\Role;

interface RoleInterface
{
   public function getRoles();

   public function createRole($role);

   public function updateRole($role);

   public function deleteRole($role_id);
}




