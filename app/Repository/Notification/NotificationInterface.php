<?php

namespace App\Repository\Notification;


interface NotificationInterface
{
    public function insert($data);

    public function notificationRead($user);
}




