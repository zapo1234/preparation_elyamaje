<?php

namespace App\Repository\Commandeids;
use App\Models\Commandeid;

interface CommandeidsInterface
{
   
   public function getAll();//recupérer toutes les données
   
   public function getAlldate($date);// recupérer toutes donnnées en fonction des dates.

}