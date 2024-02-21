<?php

namespace App\Repository\CashMovement;

use App\Model\CashMovement;

interface CashMovementInterface
{
   public function getMovements($date);

   public function getMovementsByCaisse($caisse);

   public function addMovement($data);

   public function updateMovement($movementId, $data);

   public function deleteMovement($movementId);
}




