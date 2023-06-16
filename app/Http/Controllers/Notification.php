<?php

namespace App\Http\Controllers;

use App\Repository\Notification\NotificationRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
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


}
