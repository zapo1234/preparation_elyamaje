<?php

namespace App\Repository\User;

use App\Model\User;

interface UserInterface
{
   public function getUsers();

   public function getUsersAndRoles($withInactive = false);

   public function getUsersByRole($role);

   public function getUserById($user_id);

   public function getUserByEmail($email, $user_id = false);

   public function getUserByEmailOrdIdentifier($email, $identifier, $user_id = false);

   public function updateRoleByUser($user_id, $role_id);

   public function updateUserById($user_id, $user_name_last_name, $email, $role, $poste, $type, $identifier);

   public function updateUserDetails($user_id, $data);

   public function createUser($user_name_last_name, $email, $role, $password, $poste, $type, $identifier);

   public function deleteUser($user_id);

   public function updateUserActive($email);

   public function updateUserActiveById($id);

   public function insertToken($email, $token);

   public function getUserByToken($token);

   public function updatePassword($token, $password_hash);

   public function updatePictureById($user_id, $picture);
}




