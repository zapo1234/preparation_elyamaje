<?php

namespace App\Repository\Tiers;

use Hash;
use Exception;
use Carbon\Carbon;
use App\Models\Tier;
use App\Http\Service\Api\Api;
use Illuminate\Support\Facades\DB;
use App\Http\Service\Api\PdoDolibarr;

class TiersRepository implements TiersInterface

{

   private $model;

   private $emails =[];


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

    

   /**
   * @return array
    */
    public function getEmails(): array
    {
      return $this->emails;
    }
   
   
    public function setEmails(array $emails)
    {
       $this->emails = $emails;
       return $this;
     }

    public function getalltiers()
    {
      // recupérer 
       $data =  DB::table('tiers')->select('socid','email','code_client','phone')->get();
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
        $data =  DB::table('tiers')->select('socid','email')->get();
        // transformer les retour objets en tableau
        $list = json_encode($data);
        $lists = json_decode($data,true);
        $list_code =[];
        $list_mail=[];
        
        foreach($lists as $key =>  $values){
            $list_code[$values['socid']] = $key;
            $list_mail[$values['email']] = $key;
        }
         
          $this->setEmails($list_mail);
          return $list_code;
      }

       public function insertiers()
       {
           // inseré des clients de dolibar // connecté l'api dolibar tiers sous 3 jours .
           // $method = "GET";
               //    $apiKey = env('KEY_API_DOLIBAR'); 
               //   $apiUrl = env('KEY_API_URL');

              
             
                $method = "GET";
               // key et url api
                 $apiKey = env('KEY_API_DOLIBAR'); 
                 $apiUrl = env('KEY_API_URL');

               
               /*    $produitParam = array(
                    'apikey' => $apiKey,
                    'sqlfilters' => "t.datec >= '".date("Y-m-d", strtotime("-30 days"))." 00:00:00' AND t.datec <= '".date("Y-m-d")." 23:59:59'",
                     'limit' => 0,
                    'sortfield' => 'rowid',
                    'sortorder' => 'DESC',
                );

            */

             // recuperer les données api dolibar copie projet tranfer x.

              // recuperer le dernier id => socid du tiers dans dolibarr.
              $clientSearch = json_decode($this->api->CallAPI("GET", $apiKey, $apiUrl."thirdparties", array(
                "sortfield" => "t.rowid", 
                "sortorder" => "DESC", 
                "limit" => "250", 
                 "mode" => "1",
                 )
                  ), true);
              
               //$listinvoice = $this->api->CallAPI("GET", $apiKey, $apiUrl."thirdparties", $produitParam);
              // $lists = json_decode($listinvoice,true);

              $lists = $clientSearch;


             $data_ids = array('3087');
             $code_client = array('CU2306-14213','CU2306-14212','CU2308-16399');
             // recupérer les données essentiel
             $array_tiers = $this->getallsocid();
             $array_email = $this->getEmails();

            foreach($lists as $key=>$values){
               
               if($this->testing($array_tiers,$values['id'])==false){
               
                   if(!in_array($values['id'],$data_ids)) {
                    
                    if($values['client']==1 OR $values['client']==3){
                          $x = date('Y-m-d H:i:s', $values['date_creation']);
                          $x1 = date("Y-m-d", strtotime($x.'+ 0 days'));
                          $x2 = date("Y-m-d H:i:s", strtotime($x.'+ 0 days')); // AJOUTER +1 ans MAI 2024
                          $y = date('Y-m-d H:i:s', $values['date_modification']);
                          $y2 = date("Y-m-d H:i:s", strtotime($y.'+ 0 days')); // AJOUTER +1 en Mai 2024 rappel
                           // Insert dasn la table.
                           $tier = new Tier;
                           $tier->nom = $values['name'];
                           $tier->prenom = $values['name_alias'];
                           $tier->socid = $values['id'];
                           $tier->email = $values['email'];
                           $tier->code_client = $values['code_client'];
                           $tier->phone = $values['phone'];
                           $tier->adresse = $values['address'];
                           $tier->zip_code = $values['zip'];
                           $tier->ville = $values['town'];
                            // save clients
                           $tier->save();
                 
                      }
                
                  }
                
            }
               
             
           }
     

      }

      public function insert($name,$prenom,$socid,$code_client,$email,$phone,$adresse,$zipcode,$ville,$date_created){
         // faire un insert...
          $tier = new Tier;
          $tier->nom = $name;
          $tier->prenom = $prenom;
          $tier->socid = $socid;
          $tier->code_client = $code_client;
          $tier->email = $email;
          $tier->phone = $phone;
          $tier->adresse = $adresse;
          $tier->zip_code = $zipcode;
          $tier->ville = $ville;
          $tier->date_created = $date_created;
          $tier->save();
                 
      }


      public function getinvoices($datet)
      {
   
           $method = "GET";
           // key et url api

            $apiKey = env('KEY_API_DOLIBAR'); 
            $apiUrl = env('KEY_API_URL');

        
           $produitParam = array(
             'apikey' => $apiKey,
             'sqlfilters' => "t.datec >= '".date($datet, strtotime("-0 days"))." 00:00:00' AND t.datec <= '".date($datet, strtotime("+1 days"))." 23:59:59'",
              'limit' => 0,
             'sortfield' => 'rowid',
             'sortorder' => 'DESC',
         );

          // recuperer les données api dolibar copie projet tranfer x.....
           $listinvoice = $this->api->CallAPI("GET", $apiKey, $apiUrl."invoices", $produitParam);
           $lists = json_decode($listinvoice,true);

          //  $pdoDolibarr = new PdoDolibarr(env('DB_HOST_2'),env('DB_DATABASE_2'),env('DB_USERNAME_2'),env('DB_PASSWORD_2'));

          //  $lists = $pdoDolibarr->getFk_factureByCondition($produitParam['sqlfilters']);

          //  dd($lists);

            
           return $lists;
     }


     public function getdatasids(){

         
        $method = "GET";
        // key et url api

         $apiKey = env('KEY_API_DOLIBAR'); 
         $apiUrl = env('KEY_API_URL');
        
         $produitParam = array(
             'apikey' => $apiKey,
             'sqlfilters' => "t.datec >= '".date("Y-m-d", strtotime("-10 days"))." 00:00:00' AND t.datec <= '".date("Y-m-d")." 23:59:59'",
              'limit' => 0,
             'sortfield' => 'rowid',
             'sortorder' => 'DESC',
         );


          $listinvoice = $this->api->CallAPI("GET", $apiKey, $apiUrl."invoices", $produitParam);
          $lists = json_decode($listinvoice,true);

          return $lists;
    
     }


     public function controle(){
        $x =  $this->tiers->getdatasids();
        foreach($x as $valu){
         $date = date('Y-m-d', $valu['datem']);
         foreach($valu['array_options'] as $val){
           if($val!=""){
              $list[] =(int)$valu['array_options']['options_idw'];
         }
       }
      }

       $y = array_unique($list);
       $z = array_filter($y);

       // recupérer les ids de produits dans ce intervale.
       $status ="finished";
       $posts = History::where('status','=',$status)->get();
       $name_list = json_encode($posts);
       $name_lists = json_decode($posts,true);

          foreach($name_lists as $value){
              $datev = explode('T',$value['created_at']);
              $list_array[] = $value['order_id'];
              $chaine_date = $datev[0].','.$value['order_id'];
              $result_csv[$chaine_date] = $value['order_id'];
           }


           $array_diff = array_diff($list_array,$z);
           
           $resultat_csv = [];
           foreach($array_diff as $values){
                $date_x = array_search($values, $result_csv);
                $date = explode(',',$date_x);

                $resultat_csv[] = [
                 'date_creation_preparation' => $date[0],
                 'id-commande' => $values,

                ];

           }
            
           // tirer un csv
            $this->csvcreate_product($resultat_csv);
            dd($resultat_csv);

            // tierer le csv
    }
    function getAllColoneByid($socid){

        $data =  DB::table('tiers')
            ->where('socid','=',$socid)
            ->get()
            ->toArray()
            ;

        return $data;

    }

}