<?php

namespace App\Repository\Colissimo;

interface ColissimoInterface
{
   public function getConfiguration();
   
   public function save($data);
}