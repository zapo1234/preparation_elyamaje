<?php

namespace App\Repository\Commandeids;

use App\Models\Commandeid;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CommandeidsRepository implements CommandeidsInterface
{
     
     public function __construct(Commandeid $model)
     {
        $this->model = $model;
        
     }
    
    
      public function getAll()
     {
        // recupérer tous les data de la table
        $data =  DB::table('commandeids')->select('id_commande')->get();
        $name_list = json_encode($data);
        $name_list = json_decode($data,true);
        
        return $name_list;
      
    }
    
    public function getAlldate($date)
    {
         // recupérer tous les data de la table
        $data =  DB::table('commandeids')->select('id_commande')->where('date','=',$date)->get();
        $name_list = json_encode($data);
        $name_list = json_decode($data,true);
        
        return $name_list;
        
        
    }
    
    
  
    
    
}