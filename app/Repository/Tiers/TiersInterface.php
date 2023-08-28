<?php

namespace App\Repository\Tiers;


interface TiersInterface
{
   public function getallsocid();// recupérer les socids des clients existant.

   public function insertiers(); // mettre à jours les clients dans dolibar.

   public function getalltiers();// recupérer tous les tiers en bdd.

   public function gettiersid($id);// recupérer l'utilisateur.

   public function insert($array_tiers);// créer un user direct.
}
