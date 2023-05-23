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
      $message="";
      return view('tiers.refreshtiers',['message'=>$message]);

    }

    public function postiers()
    { 
       // recupérer le traitement des tiers pour les inserts dans la table.
       $this->tiers->insertiers();// mise à jours des tiers.......
       $message="les clients sont bien mis à jours.";
       return view('refreshtiers',['message'=>$message]);

    }

    
  }




