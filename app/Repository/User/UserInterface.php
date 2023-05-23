<?php

namespace App\Repository\User;

use App\Model\User;

interface UserInterface
{
   public function getUsers();

   public function getUsersByRole($role);

   public function updateRoleByUser($user_id, $role_id);
}




