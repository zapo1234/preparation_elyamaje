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
               //construire les données à flush dans les tables orders_dol et order_product_dol.
               $data_ids =[];// recupérer les ids socid et recupérer l'utilisateur ensuite depuis la table tiers.
               $data_orders_dol =[];
               $data_orders_product =[];

               foreach($lists as $value){
                  
                $x = date('Y-m-d', $vals['date_creation']);
                 // ajouter un jour $x
                $x1  = date("Y-m-d", strtotime($x.'+ 1 days'));// date de création de la commande via dolibarr.

                $data_ids[] = $value['socid'];
                $data_order_dol[] =[
                 'socid' => $value['socid'],
                 'date_creation'=> $x1,
                 'order_id'=>$value['id'],
                 'total_ht' =>floatval($value['total_ht']),
                 'total_tva'=>floatval($value['total_tva']),
                 'total_ttc'=>floatval($value['total_ttc'])
                
                 ];

                  foreach($value['lines'] as $val) {
                   
                    $data_orders_dol[] =[
                     'order_id' => $value['id'],
                     'data_product'=>[
                      "ref"=>$val['ref'],
                      "product_label"=>$val['product_label'],
                      "product_barcode"=>$val['product_barcode'],


                     ]


                     ];

                  

                    }
               
               
         }    
               

        }

}

     