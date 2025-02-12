<?php

namespace App\Repository\Commandeids;
use App\Models\Commandeid;

interface CommandeidsInterface
{
   
   public function getAll();//recupérer toutes les données
   
   public function getAlldate($date);// recupérer toutes donnnées en fonction des dates.

   public function getIdcountry();// recupérer id du du pay client.

   public function getIdsinvoices($id_commande);// recupérer id de la commande...

   public function getIdsfkfacture();

   public function createpaiementid();

   public function deleteOrder($order_id); // Supprime une commande afin de pouvoir refacturer

   public function getrowidfacture();// recupere rowid de la table.

   public function insertProductOrder($data); // recupere rowid de la table.
}