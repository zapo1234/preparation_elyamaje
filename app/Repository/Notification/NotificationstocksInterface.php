<?php

namespace App\Repository\Notification;


interface NotificationstocksInterface
{
    public function insert($data);// inserer les lignes de mouvement de stock de lot.

    public function deletedatable();

     public function  getAll();

     public function getAlls();

}


