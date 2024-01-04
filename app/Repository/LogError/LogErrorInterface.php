<?php

namespace App\Repository\LogError;


interface LogErrorInterface
{
    public function insert($data);

    public function getAllLogs();
}




