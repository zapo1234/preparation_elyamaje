<?php
namespace App\Http\Service\CallApi;

use Illuminate\Support\Facades\Http;
use Automattic\WooCommerce\Client;
use Automattique\WooCommerce\HttpClient\HttpClientException;
use DateTime;
use DateTimeZone;

class TransfertOrderdol
{
    
      private $api;
      
      private $commande;
      private $status; // vartiable string pour statuts(customer et distributeur)
     
    
       public function __construct(Apicall $api)
       {
         $this->api=$api;
         
       }
    
    
     /** 
     *@return array
     */
      public function getordersdol()
      {
         // recuperer les commandes dans dolibar
         $method = "GET";
         $apiKey = env('KEY_API_DOLIBAR');
         $apiUrl = env('KEY_API_URL');
            $produitParam = array(
              'apikey' => $apiKey,
              'sqlfilters' => "t.datec >= '".date("Y-m-d", strtotime("-3 days"))." 00:00:00' AND t.datec <= '".date("Y-m-d")." 23:59:59'",
               'limit' => 0,
              'sortfield' => 'rowid',
              'sortorder' => 'DESC',
          );

           
               // appel du service via api
               $listinvoice = $this->api->CallAPI("GET", $apiKey, $apiUrl."orders", $produitParam);
               $lists = json_decode($listinvoice,true);

               // recupérer les orders depuis la prod
               dd($lists);
               
               
              
               
   }


}

     