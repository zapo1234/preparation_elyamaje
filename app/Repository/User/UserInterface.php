<?php

namespace App\Repository\User;

use App\Model\User;

interface UserInterface
{
   public function getUsers();

   public function getUsersByRole($role);

   public function getUserById($user_id);

   public function getUserByEmail($email, $user_id = false);

   public function updateRoleByUser($user_id, $role_id);

   public function updateUserById($user_id, $user_name_last_name, $email, $role, $poste, $type);

   public function createUser($user_name_last_name, $email, $role, $password, $poste, $type);

   public function deleteUser($user_id);

   public function updateUserActive($email);

   public function insertToken($email, $token);

   public function getUserByToken($token);

   public function updatePassword($token, $password_hash);
}




