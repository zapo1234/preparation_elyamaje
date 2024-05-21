<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class Kit extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function kit(){
        return view('preparateur.kits.kit');
    }
}