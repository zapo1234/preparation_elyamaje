<?php

namespace App\Http\Controllers;

use DateInterval;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use App\Repository\Notification\NotificationRepository;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Notification extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    private $notificaion;

    public function __construct(NotificationRepository $notificaion){
        $this->notificaion = $notificaion;
    }

    public function notificationRead(){
        $user = Auth()->user()->id;
        return $this->notificaion->notificationRead($user);
    }

    public function allNotification(){
        $user = Auth()->user()->id;
        $notifications = $this->notificaion->getAllNotifications($user);

        foreach($notifications as $key => $notification){
            $notifications[$key]->date = $this->format_interval($notifications[$key]->created_at->diff(date('Y-m-d H:i:s')));
        }

        return view('notifications.notifications', ['notifications' => $notifications]);
    }

    private function format_interval($interval) {
        $result = "";

        if ($interval->y) { $result = $interval->format("%y an(s) "); }
        if ($interval->m) { $result .= $interval->format("%m mois "); }
        if ($interval->d && (!$interval->m && !$interval->y)) { $result .= $interval->format("%d jour(s) "); }
        if ($interval->h && (!$interval->m && !$interval->y)) { $result .= $interval->format("%h heure(s) "); }
        if ($interval->i && (!$interval->d && !$interval->m && !$interval->y)) { $result .= $interval->format("%i minute(s) "); }
        if ($interval->s && (!$interval->h && !$interval->d && !$interval->i)) { $result .= $interval->format("%s secondes "); }

        return $result;
    }
}
