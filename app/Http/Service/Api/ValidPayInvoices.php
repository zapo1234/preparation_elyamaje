<?php

namespace App\Http\Service\Api;

use PDO;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Transfertsucce;
use App\Models\Transfertrefunded;
use Automattic\WooCommerce\Client;
use App\Repository\LogError\LogErrorRepository;
use App\Models\Distributeur\Invoicesdistributeur;
use App\Repository\Commandeids\CommandeidsRepository;
use Automattique\WooCommerce\HttpClient\HttpClientException;
use App\Http\Service\Api\AddlineInvoicePay;

class ValidPayInvoices
{
    private $api;
    
  private $commande;
  private $dataidcommande;// recupérer les ids commande existant
  private $status; // vartiable string pour statuts(customer et distributeur)
  private $countd = []; // les clients distributeur
  private $countc = [];// les clients non distributeur.
  private $accountpay;
  private $distristatus;
  private $ficfacture;
  private $logError;
  private $addlineinvoice;

    public function __construct(Api $api,
    CommandeidsRepository $commande,
    LogErrorRepository $logError,
    AddlineInvoicePay $addlineinvoice
    )
    {
        $this->api=$api;
        $this->commande = $commande;
        $this->logError = $logError;
        $this->addlineinvoice = $addlineinvoice;
        
        
    }


    /**
     * valider et accorder un moyen de paiment une facture dans dolibar
     * @param string $chaine_amount une chaine de caractère envoyé
     * @param string $getAccount une chaine de caractère envoyé
     * @param string $getIsdistributor une chaine de caractère envoyé
     * @param int $getIdfacture une chaine de caractère envoyé
     * @param array $orders tableau envoyé client
     * @return void
     */
    public function ValidPay($orders,$chaine_amount,$getAccountpay,$getIsdistributor,$getIdfacture)
    {
        $method = "GET";
             $apiKey = env('KEY_API_DOLIBAR'); 
             $apiUrl = env('KEY_API_URL');
             //appelle de la fonction  Api
              $ref_order="";
              $account_name ="";
              $distristatus="";
              foreach($orders as $values){
               $ref_order = $values['order_id'];
             }
               $invoices_id = json_decode($this->api->CallAPI("GET", $apiKey, $apiUrl."invoices", array(
	    	       "sortfield" => "t.rowid", 
	    	       "sortorder" => "DESC", 
		           "limit" => "1", 
		           "mode" => "1",
	         	)
          	), true);
      	
               // recupérer le premier id de la facture
               // recuperer dans un tableau les ref_client existant id.
               $invoices_asc = json_decode($this->api->CallAPI("GET", $apiKey, $apiUrl."invoices", array(
		          "sortfield" => "t.rowid", 
		           "sortorder" => "ASC", 
		          "limit" => "1", 
	        	   "mode" => "1",
	        	)
    	      ), true);
    
             // recuperer dans un tableau les ref_client existant id.
             $clientSearch = json_decode($this->api->CallAPI("GET", $apiKey, $apiUrl."thirdparties", array(
		         "sortfield" => "t.rowid", 
		         "sortorder" => "DESC", 
		         "limit" => "1", 
		          "mode" => "1",
		      )
        	), true);

               $inv="";
              foreach($invoices_id as $vk) {
                $inv = $vk['id'];
              }
              // recupérer le premier id de la facture....
             foreach($invoices_asc as $vks){
               $inc = $vks['id'];
             }
            
             foreach($clientSearch as $data) {
               $tiers_ref = $data['id'];
             }
        
               // le nombre recupérer 
               $count_datas = $orders;// retour array ici
               $ids_orders =[];// recupérer les id commande venant de woocomerce....
               $data_ids=[];// recupérer les nouveaux ids de commande jamais utilisés....
               $data_fk_facture =[];// recupérer l'id de la commande et le fk_facture
              foreach($count_datas as $k =>$valis){
                     $ids_orders[] = $valis['id'];
                     if(!in_array($valis['id'],$this->getDataidcommande())) {
                        $data_ids[]= $valis['id'];
                      
                      }
               }
                // insérer id commande et id_facture via dolibarr.
                 $data_fk_facture[] =[
                 'id_commande'=> $getIdfacture,
                 'id_invoices'=>$inv

              ];

                  // Liee id de la commande au fk_facture et insert dans une table 
                  DB::table('fk_factures')->insert($data_fk_facture);
                  // le nombre de facture à traiter en payé
                  $count_data = count($ids_orders);
                  // les nouveau order à traiter
                   // recupérer le nombre de commande recupérer 
                 $nombre1 = $count_data;
                 //$nombre2= count($this->getDataidcommande());// compter les anciennes ids 
                 // nombre des nouveaux order recupérer journaliier.
                 $nombre_orders = count($data_ids);
                 // tranformer le tableau en chaine de caractère
                 $list_id_commande = implode(',',$data_ids);
                 $nombre_count = $inv - $nombre_orders+1;
                 $datetime = date('d-m-Y H:i:s');
                 $dat = date('Y-m-d H:i:s');
         
                 // insert infos dans bdd ...
                 if($nombre_orders= 0) {
                    $label = "Aucune commande transférée";
                 }
                  elseif($nombre_orders==1){
                     $label ="la commande à été transférée dans dolibars le $datetime";
                  }
                  else{
                      $label = "$nombre_orders commandes transférées dans dolibars le $datetime";
                 }
                 // insert dans la table 
                 // recupérer une chaine de id_commande et id_facture
                 $chaine_data = $list_id_commande.','.$inv;
                 $sucess = new Transfertsucce();
                 $sucess->date = $dat;
                 $sucess->id_commande = $list_id_commande;
                 $sucess->label = $label;
                 $sucess->save();
                 // convertir en entier la valeur.
                 $id_cl = (int)$tiers_ref;
                 $id_cl = $id_cl+1;
                  $socid ="";
                 // id  du dernier invoices(facture)
                 // valider invoice
                 $newCommandeValider = [
                "idwarehouse"	=> "6",
                 "notrigger" => "0",
                ];
                
                  // recupérer le mode de paiement
                  $account_name = $getAccountpay;
                  $status_dist = $getIsdistributor; //$donnees['is_distributor'];
                  // recupération les méthode de paiement.
                  $moyen_card = $this->commande->createpaiementid();
                  // recupérer les index_amout pour pour gérer les paiement des commandes.
                  $chaine_amount_true = $chaine_amount;
                   $index_amount_true = explode('%',$chaine_amount_true);
                   $chaine_index =",";

    
                   // recupérer le status
                   if($index_amount_true[2]=="cbliq"){
                    // ici y'a eu paiement en LIQ et CB
                      $index_m ="CB";
                      $moyen_paid =  array_search($index_m,$moyen_card);
                      $moyen_paids = explode(',',$moyen_paid);
                      $mode_reglement_id = $moyen_paids[0];
                      $account_multiple="cbliq";
                     // j'accroche les compte bancaire.
                      
                   }

                   if($index_amount_true[2]=="liqpaid"){
                    // ici y'a eu paiement uniquement en LIQ
                
                      $index_m ="LIQ";
                      $moyen_paid =  array_search($index_m,$moyen_card);
                      $moyen_paids = explode(',',$moyen_paid);
                      $mode_reglement_id = $moyen_paids[0];
                      $account_multiple="yesliq";
                        // j'accroche les compte bancaire
                    }

                    // iniquement que card kdo
                      if($index_amount_true[2]=="kdo"){
                       $index_m ="CADO";
                      $moyen_paid =  array_search($index_m,$moyen_card);
                      $moyen_paids = explode(',',$moyen_paid);
                      $mode_reglement_id = $moyen_paids[0];
                      $account_multiple="kdo";
                        // j'accroche les compte bancaire
                     }
                       // Qaund y'a un paiement uniquement que par CB 
                     if($index_amount_true[2]=="cbtotal"){
                        $index_m ="CB";
                        $moyen_paid =  array_search($index_m,$moyen_card);
                        $moyen_paids = explode(',',$moyen_paid);
                        $mode_reglement_id = $moyen_paids[0];
                        $account_multiple="cbtotal";
                       
                    }

                    // quand y'a eu espece et card cado
                    if($index_amount_true[3]=="liqkdo"){
                       $index_m ="LIQ";
                      $moyen_paid =  array_search($index_m,$moyen_card);
                      $moyen_paids = explode(',',$moyen_paid);
                      $mode_reglement_id = $moyen_paids[0];
                      $account_multiple="liqkdo";

                     }

                       // quand y'a une carte bancaire et cado
                     if($index_amount_true[2]=="cbcado"){

                          $index_m ="CB";
                          $moyen_paid =  array_search($index_m,$moyen_card);
                          $moyen_paids = explode(',',$moyen_paid);
                           $mode_reglement_id = $moyen_paids[0];
                          $account_multiple="cbcado";

                     }

                     // quand y'a eu les 3 a meme temps(cb,espece,cardko);
                     if($index_amount_true[3]=="tous"){
                        $index_m ="CB";
                        $moyen_paid =  array_search($index_m,$moyen_card);
                        $moyen_paids = explode(',',$moyen_paid);
                        $mode_reglement_id = $moyen_paids[0];
                        $account_multiple="yestous";
                        
                     }
                     
                       // si lacommande ne vient pas par un Gala(Marseille)
                    if($index_amount_true[3]=="nobpp"){
                         
                       $moyen_paid =  array_search($account_name,$moyen_card);
                           if($moyen_paid!=false){
                            $moyen_paids = explode(',',$moyen_paid);
                             $mode_reglement_id = $moyen_paids[0];
                          }else{
                           $account_name="payplug";
                            $mode_reglement_id =106;// fournir un paypplug par defaut. au cas il trouve pas.....
                          }

                          $account_multiple="nogala";
                     }
                    

                  $array_paiment = array('cod','vir_card1','vir_card','payplug','stripe','oney_x3_with_fees','oney_x4_with_fees','apple_pay','american_express','gift_card','bancontact','CB','PAYP');// carte bancaire....
                   $array_paiments = array('bacs', 'VIR');// virement bancaire id.....
                   $array_paimentss = array('DONS');
                   $array_espece =  array('LIQ');
                   $double_pai = array('CB,LIQ','LIQ,CB');// recupérer la methode de paiment....
                   $array_revolut = array('revolut_pay','revolut_cc');
                   $array_scalapay = array('wc-scalapay-payin3','wc-scalapay-payin4');
                   $array_paiments4 = array('CHQ');// chéque.
                   $array_facture_dolibar = array('VIR');
                   // commande vient pas du gala donc par internet ou devis
                  if($account_multiple=="nogala"){
                      if(in_array($account_name,$array_paiment)) {
                       $account_id=4;// PROD 
                       $paimentid =6;// PROD
                     }

                     elseif(in_array($account_name,$array_revolut)) {
                         // revolut nouveaux compte.
                        // voir id moyens de paiment dans la table de dolibar paiment.
                          //$account_id=49;
                           $account_id =51;// prod 
                         if($account_name=="revolut_pay"){
                           //$paimentid =55;// transfertx
                            $paimentid = 110;// prod
                         }
  
                         if($account_name=="revolut_cc"){
                           // $paimentid=107; // transfertx
                            $paimentid=109; // prod
                         }
                         
                     }

                     elseif(in_array($account_name,$array_scalapay)){
                      // defini le paiment comme virement bancaire......
                       $account_id = 52; // PROD
                       $paimentid = 111;// PROD
                    }
                 
                       elseif(in_array($account_name,$array_paiments)){
                         $account_id=3; // PROD
                         $paimentid =3;// PROD
                       }

                       elseif(in_array($account_name,$array_paiments4)){
                        // cheque
                         $account_id=5; // PROD
                         $paimentid=5;// PROD
                        }

                       elseif(in_array($account_name,$array_paimentss)){
                       // DONS
                         $account_id=3; // PROD
                         $paimentid=3;// PROD
                     }

                     else{

                      $account_id=4;// PROD 
                      $paimentid =6;// PROD
                     }
                }
                  
                   // 100%liquide.
                   if($account_multiple=="yesliq"){
                        $account_id=50;
                        $paimentid =4;//
                   }
                    
                   // liquide cdo
                   if($account_multiple=="liqkdo"){
                      $account_id=50;
                       $paimentid =4;// 
                   }
                   // liquide et cb 
                   if($account_multiple=="cbliq"){
                      //$account_id=48;// transfertx
                       $account_id=49;  //PROD
                      $paimentid =6;// PROD envoi en CB.
                 }
                 
                 // tous cb/liq/espece
                 if($account_multiple=="yestous"){
                  //$account_id=48;// transfertx 
                   $account_id =49; // prod
                  $paimentid =6;// PROD envoi en CB.
                 }

                 // qaund y'a eu cb et carte cadeaux
                 if($account_multiple=="cbcado"){
                     //$account_id=48;// transfertx
                     $account_id =49; // prod
                     $paimentid =6;// PROD envoi en CB.

                 }

                 // qaund y'des carte kdo
                 if($account_multiple=="kdo"){
                     //$account_id=51; // transfertx
                      $account_id =53; //prod
                     $paimentid=57; // transfertx && prod

                 }

                 // quand y'a eu uniquement CB
                 if($account_multiple=="cbtotal"){
                     //$account_id =48; // transfertx
                    $account_id =49; // prod
                     $paimentid =6;

                 }

                  // si c'est un distributeur (mettre la facture impayé)
                    if($status_dist=="true" && $account_name=="bacs"){
                      $newCommandepaye = [
                      "paye"	=> 1,
                      "statut"	=> 2,
                      "mode_reglement_id"=>$mode_reglement_id,
                      "idwarehouse"=>6,
                      "notrigger"=>0,
                     ];
                       
                       $valid=1;// mettre la facture impayés.
                  }
                   if($status_dist=="true" && $account_name!="bacs"){
                       $newCommandepaye = [
                       "paye"	=> 1,
                       "statut"	=> 2,
                       "mode_reglement_id"=>$mode_reglement_id,
                      "idwarehouse"=>6,
                      "notrigger"=>0,
                       ];
                        
                         $valid=2;
                     }

                    if($status_dist!="true" && $account_name!="VIR"){
                     // $mode reglement de la facture ....
                      $newCommandepaye = [
                      "paye"	=> 1,
                     "statut"	=> 2,
                      "mode_reglement_id"=>$mode_reglement_id,
                      "idwarehouse"=>6,
                       "notrigger"=>0,
                     ];
                         $valid=3;

                  }

                    // si la commande vient de dolibar et account_name est vir(facture impayée)
                    $chaine_prefix="CO";
                    if(strpos($ref_order,$chaine_prefix)!==false && $account_name=="VIR"){
                        $newCommandepaye = [
                        "paye"	=> 1,
                        "statut"	=> 2,
                        "mode_reglement_id"=>$mode_reglement_id,
                       "idwarehouse"=>6,
                       "notrigger"=>0,
                        ];
                      
                         $valid =1;

                      }
      

                  $newCommandepaye = [
                    "paye"	=> 1,
                   "statut"	=> 2,
                    "mode_reglement_id"=>$mode_reglement_id,
                    "idwarehouse"=>6,
                     "notrigger"=>0,
                   ];

                 // recupérer la datetime et la convertir timestamp
                 // liée la facture à un mode de rélgement
                // convertir la date en datetime en timestamp.....
                //$datetime = date('d-m-Y H:i:s');
                $datetime = date('d-m-Y H:i:s', strtotime('-2 hours'));// a modifier selon le décalage horaire.(ajouter heure)
                $d = DateTime::createFromFormat(
                'd-m-Y H:i:s',
                 $datetime,
                 new DateTimeZone('UTC')
             );
   
            if($d === false) {
                   die("Incorrect date string");
              } else {
              $date_finale =  $d->getTimestamp(); // conversion de date.
             }
    
               $newbank = [
               "datepaye"=>$date_finale,
               "paymentid"=>$paimentid,
               "closepaidinvoices"=> "yes",
               "accountid"=> $account_id, // id du compte bancaire.
               ];

              
              // valider les facture dans dolibar...
              if ($valid == 1) {
                // Valider la facture en impayée
                $this->api->CallAPI("POST", $apiKey, $apiUrl . "invoices/" . $inv . "/validate", json_encode($newCommandeValider));
            } else {
                // Valider et mettre en payée la facture
                $validate_facture = ""; // Retour de traitement de l'API
                $validate_facture = $this->api->CallAPI("POST", $apiKey, $apiUrl . "invoices/" . $inv . "/validate", json_encode($newCommandeValider));
            
                // Traiter la réponse de l'API
                $response = json_decode($validate_facture, true);
                $index_facture = "FA"; // Facture valide
                $index_facture1 = "PR"; // Détecter une erreur sur la validation souhaitée d'une facture
            
                if (!isset($response['ref'])) {
                    $this->logError->insert([
                        'order_id' => isset($orders[0]['order_woocommerce_id']) ? $orders[0]['order_woocommerce_id'] : 0,
                        'message' => 'Erreur de validation de la facture restée impayée, veuillez la valider !'
                    ]);
                    echo json_encode(['success' => false, 'message' => 'Erreur de validation de la facture restée en brouillons, veuillez la valider !']);
                    exit;
                }
            
                if (isset($response['error']['message'])) {
                    $message = $response['error']['message'];
                    $this->logError->insert([
                        'order_id' => isset($orders[0]['order_woocommerce_id']) ? $orders[0]['order_woocommerce_id'] : 0,
                        'message' => $message
                    ]);
                    echo json_encode(['success' => false, 'message' => $message]);
                    exit;
                }
            
                  // Mettre le statut en payé dans la facture Dolibarr des préparations (uniquement internet)
                  if ($account_multiple == "nogala") {
                      // Lier les factures Dolibarr à un moyen de paiement et banque
                      $this->api->CallAPI("POST", $apiKey, $apiUrl . "invoices/" . $inv . "/payments", json_encode($newbank));
                      $this->api->CallAPI("PUT", $apiKey, $apiUrl . "invoices/" . $inv, json_encode($newCommandepaye));
                  }
              
                  if ($account_multiple == "yesliq") { // Liquide 100% GALA // 
                      // Lier les factures Dolibarr à un moyen de paiement et banque
                      $this->api->CallAPI("POST", $apiKey, $apiUrl . "invoices/" . $inv . "/payments", json_encode($newbank));
                      $this->api->CallAPI("PUT", $apiKey, $apiUrl . "invoices/" . $inv, json_encode($newCommandepaye));
                  }
              
                  if ($account_multiple == "kdo") { // 100% cadeaux
                     // modifier la ligne de paiment pour attribué un debit.
                      $ref = $response['ref']; 
                      $this->addlineinvoice->reconstruirecdo($inv,$ref,$newCommandepaye, $newbank,$apiKey, $apiUrl);
                  }
              
                  if ($account_multiple == "cbtotal") { // 100% CB
                      // Lier les factures Dolibarr à un moyen de paiement et banque
                      $this->api->CallAPI("POST", $apiKey, $apiUrl . "invoices/" . $inv . "/payments", json_encode($newbank));
                      $this->api->CallAPI("PUT", $apiKey, $apiUrl . "invoices/" . $inv, json_encode($newCommandepaye));
                  }
              
                  if ($account_multiple == "cbliq") {
                      // Les cas où il y a des paiements en partie espèces et CB pour le Gala
                      $montant1 = $index_amount_true[0]; // CB
                      $montant2 = -$index_amount_true[1]; // Carte cadeau
                      $ref = $response['ref'];
                      $this->addlineinvoice->AddlinepayInvoices($inv, $montant1, $montant2, $ref, $newCommandepaye, $newbank, $apiKey, $apiUrl);
                  }
              
                  if ($account_multiple == "liqkdo") {
                      // Quand il y a eu liquide Gala et cadeau
                      $montant1 = $index_amount_true[1]; // Liquide
                      $montant2 = -$index_amount_true[2]; // Carte cadeau
                      $ref = $response['ref'];
                      $this->addlineinvoice->Addlinepaykdo($inv, $montant1, $montant2, $ref, $newCommandepaye, $newbank, $apiKey, $apiUrl);
                  }
              
                  if ($account_multiple == "cbcado") {
                      // Quand il y a eu CB et cadeau
                      $montant1 = $index_amount_true[0]; // CB
                      $montant2 = -$index_amount_true[1]; // Carte cadeau
                      $ref = $response['ref'];
                      $this->addlineinvoice->Addlinepaykdo($inv, $montant1, $montant2, $ref, $newCommandepaye, $newbank, $apiKey, $apiUrl);
                  }
              
                  if ($account_multiple == "yestous") {
                      // CB / Cadeau / Espèce
                      $montant1 = $index_amount_true[0]; // CB
                      $montant2 = $index_amount_true[1]; // Espèce
                      $montant3 = -$index_amount_true[2]; // Bon d'achat
                      $ref = $response['ref'];
                      // Ajouter des lignes de paiement (espèce, reconstruire le montant CB)
                      $this->addlineinvoice->AddlinepayInvoice($inv, $montant1, $montant2, $montant3, $ref, $newCommandepaye, $newbank, $apiKey, $apiUrl);
                  }
              }
    
   }

}
