<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
use App\Repository\Tiers\TiersRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TiersController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private $tiers;

    public function __construct(TiersRepository $tiers){
     $this->tiers = $tiers;
      
    }


    public function getiers()
    {
      return view('Tiers.refreshtiers');

    }

    
  }




