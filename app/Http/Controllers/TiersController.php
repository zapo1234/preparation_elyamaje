<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Service\Api\Api;
use App\Repository\Tiers\TiersRepository;
use App\Http\Service\Api\TransferOrder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TiersController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private $tiers;

    public function __construct(
    Api $api,
    TiersRepository $tiers,
    TransferOrder $orders){
     $this->tiers = $tiers;
     $this->api = $api;
     $this->orders = $orders;
      
    }


    public function getiers()
    {
      $message="";
      return view('tiers.refreshtiers',['message'=>$message]);

    }

    public function postiers()
    { 
       // recupérer le traitement des tiers pour les inserts dans la table.
       $date_after="2023-08-2023";
       $date_before="2023-09-2023";
       $this->orders->Transferorder($date_after,$date_before);
       //$this->tiers->insertiers();// mise à jours des tiers.......
       $message="les clients sont bien mis à jours.";
       return view('refreshtiers',['message'=>$message]);

    }

    
  }




