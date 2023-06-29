<?php

namespace App\Repository\Tiers;

use Hash;
use Exception;
use Carbon\Carbon;
use App\Models\Tier;
use App\Http\Service\Api\Api;
use Illuminate\Support\Facades\DB;

class TiersRepository implements TiersInterface

{

   private $model;

   public function __construct(Tier $model,Api $api){

      $this->model = $model;
      $this->api = $api;
   }


    public function testing($array_x,$val)
    {
       if(isset($array_x[$val]))
       {
           return true;
       }
       
       else{
           return false;
       }
    }

    public function getalltiers()
    {
      // recupérer 
       $data =  DB::table('tiers')->select('socid','email','code_client')->get();
       // transformer les retour objets en tableau
       $list = json_encode($data);
       $lists = json_decode($data,true);
       
       return $lists;


    }


    public function gettiersid($id)
    {
      // recupérer 
       $data =  DB::table('tiers')->select('nom','email')->where('socid','=',$id)->get();
       // transformer les retour objets en tableau
       $list = json_encode($data);
       $lists = json_decode($data,true);
       
       return $lists;


    }

    public function getallsocid()
    {
        $data =  DB::table('tiers')->select('socid')->get();
        // transformer les retour objets en tableau
        $list = json_encode($data);
        $lists = json_decode($data,true);

        dd($lists);
        $list_code =[];
        
        foreach($lists as $key =>  $values){
            $list_code[$values['socid']] = $key;
        }
          return $list_code;
      }

       public function insertiers()
       {
           // inseré des clients de dolibar // connecté l'api dolibar tiers sous 3 jours .
          // $method = "GET";
          // $apiKey ="9W8P7vJY9nYOrE4acS982RBwvl85rlMa";
         //  $apiUrl ="https://www.poserp.elyamaje.com/api/index.php/";
             
         dd('zapo');
               // recuperer les données api dolibar copie projet tranfer x.
               $method = "GET";
               $apiKey = env('KEY_API_DOLIBAR');
               $apiUrl = env('KEY_API_URL');
                  $produitParam = array(
                    'apikey' => $apiKey,
                    'sqlfilters' => "t.datec >= '".date("Y-m-d", strtotime("-30 days"))." 00:00:00' AND t.datec <= '".date("Y-m-d")." 23:59:59'",
                     'limit' => 0,
                    'sortfield' => 'rowid',
                    'sortorder' => 'DESC',
                );

         
    
            $listinvoice = $this->api->CallAPI("GET", $apiKey, $apiUrl."thirdparties", $produitParam);
            $lists = json_decode($listinvoice,true);
            $data_ids = array('3087');
            // recupérer les données essentiel


           $array_tiers = $this-> getallsocid();
     

      }

}