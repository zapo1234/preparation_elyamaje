<?php
namespace App\Http\Service\Api;

use Illuminate\Support\Facades\Http;
use App\Models\Commandeid;
use App\Models\Productdiff;
use App\Models\Transfertrefunded;
use App\Models\Transfertsucce;
use App\Models\Don;
use App\Models\Distributeur\Invoicesdistributeur;
use App\Repository\Commandeids\CommandeidsRepository;
use App\Repository\Don\DonRepository;
use App\Repository\Don\DonsproductRepository;
use App\Repository\Tiers\TiersRepository;
use Automattic\WooCommerce\Client;
use Automattique\WooCommerce\HttpClient\HttpClientException;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\DB;

class TransferOrder
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
    
       public function __construct(
        Api $api,
        CommandeidsRepository $commande,
        TiersRepository $tiers,
        DonRepository $don,
        DonsproductRepository $dons
       )
       {
         $this->api=$api;
         $this->commande = $commande;
         $this->tiers = $tiers;
         $this->don = $don;
         $this->dons = $dons;
       }
    
    
      public function getOrderWoocomerce()
      {
        // recupérer les orders mise à jours en fonction des status souhaités
        //competed,processing,
     
      }
      
      
      
      /**
   * @return array
    */
   public function getDataidcommande(): array
   {
      return $this->dataidcommande;
   }
   
   
   public function setDataidcommande(array $dataidcommande)
   {
     $this->dataidcommande = $dataidcommande;
    return $this;
   }
   
   /**
   * @return array
    */
   public function getCountd(): array
   {
      return $this->countd;
   }
   
   
   public function setCountd(array $countd)
   {
     $this->setCountd = $countd;
    return $this;
   }
   
   
   
        /**
   * @return array
    */
   public function getCountc(): array
   {
      return $this->countc;
   }
   
   
   public function setCountc(array $countc)
   {
     $this->setCountc = $countc;
    return $this;
   }
   
  
   public function getAccountpay()
   {
      return $this->accountpay;
   }
   
   
   public function setAccountpay($accountpay)
   {
      $this->accountpay = $accountpay;
      return $this;
   }


   public function getDistristatus()
   {
      return $this->distristatus;
   }
   
   
   public function setDistristatus($distristatus)
   {
      $this->distristatus = $distristatus;
      return $this;
   }




   public function getFicfacture()
   {
       return $this->ficfacture;
   }
   
   
   public function setFicfacture($ficfacture)
   {
      $this->ficfacture = $ficfacture;
      return $this;
   }



    public  function createtiers($donnees)
    {
        //
         $apiKey = env('KEY_API_DOLIBAR'); 
         $apiUrl = env('KEY_API_URL');
         $socid = $this->api->CallAPI("POST", $apiKey, $apiUrl."thirdparties", json_encode($donnees));
         return $socid;
         
    }
     
     
   


     /** 
     *@return array
     */
      public function Transferorder($orders)
      {
           
           $this->getfacture($orders);

           dd('zapo');
    
             $fk_commande="";
             $linkedObjectsIds =[];
             $coupons="";
             $emballeur="";
             $preparateur="";
             foreach($orders as $val){
                 if(isset($val['fk_commande'])){
                    $id_commande="exist";
                    $linkedObjectsIds =  ["commande" => [""=>$val['fk_commande']]];
                    $emballeur = $val['emballeur'];
                    $preparateur="";
                    $coupons="";
                 }

                 else{
                     $coupons = $val['coupons'];
                     $preparateur = $val['preparateur'];
                     $emballeur = $val['emballeur'];
                     $linkedObjectsIds = [];
                    
                 }
             }
             
                 $method = "GET";
                 // recupérer les clé Api dolibar transfertx........
                 $apiKey = env('KEY_API_DOLIBAR'); 
                 $apiUrl = env('KEY_API_URL');


                 $produitParam = ["limit" => 1600, "sortfield" => "rowid"];
	               $listproduct = $this->api->CallAPI("GET", $apiKey, $apiUrl."products", $produitParam);
                 // reference ref_client dans dolibar
                   $listproduct = json_decode($listproduct, true);// la liste des produits dans doliba.

                if(count($listproduct)==0){
                   echo json_encode(['success' => false, 'message'=> ' la facture n\'a pas été crée signalé au service informatique !']);
                    exit;
                  }
                  //Recuperer les ref_client existant dans dolibar
	                $tiers_ref = "";
                  // recupérer directement les tiers de puis bdd.
                  //$this->tiers->insertiers();// mise a jour api
                  $list_tier = $this->tiers->getalltiers();// recupérer les tiers a jours ..
                  // recuperer les ids commandes
                  $ids_commande = $this->commande->getAll(); // tableau pour recupérer les id_commande 
                  $key_commande = $this->commande->getIds();// lindex les ids commande existant.
                 // recupérer le tableau de ids
                  $ids_commandes =[];
                  foreach($ids_commande as $key => $valis) {
                     $ids_commandes[$valis['id_commande']] = $key;
                  }

                  // recupérer l'id du pays du clients associé au prféfixe du pays.
                  $data_id_country = $this->commande->getIdcountry();
                  $data_ids_country = [];
                  foreach($data_id_country as $valu){
                     $data_ids_country[$valu['rowid']]= $valu['code'];
                  }

                  // recupérer les email,socid, code client existant dans tiers
                  $data_email = [];//entre le code_client et email.
                  $data_list = []; //tableau associative de id et email
                  $data_code =[];// tableau associative entre id(socid et le code client )
                  $data_phone = [];
                  foreach($list_tier as $val) {
                    //$data_email[$val['code_client']] = $val['email'];
                     if($val['email']!="") {
                       $data_list[$val['socid']] = mb_strtolower($val['email']);
                     }
                     
                      if($val['phone']!=""){
                        $data_phone[$val['socid']] = $val['phone'];
                     }
                     
                      // recuperer id customer du client et créer un tableau associative.
                      $code_cl = explode('-',$val['code_client']);
                      if(count($code_cl)>2){
                        $code_cls = $code_cl[2];
                        if($code_cls!=0){
                          $data_code[$val['socid']] = $code_cls;
                        }
                      }
                  }

                 // recuperer le dernier id => socid du tiers dans dolibarr.
                  $clientSearch = json_decode($this->api->CallAPI("GET", $apiKey, $apiUrl."thirdparties", array(
		              "sortfield" => "t.rowid", 
	    	          "sortorder" => "DESC", 
		              "limit" => "1", 
		               "mode" => "1",
		               )
         	         ), true);

                  foreach($clientSearch as $data) {
                    $tiers_ref = $data['id'];
                 }
                  // convertir en entier la valeur le dernier id du tiers=>socid.
                   $id_cl = (int)$tiers_ref;
                   $id_cl = $id_cl+1;
                   $socid ="";
                   $data_list_product =[];// tableau associative entre le ean barcode et id_produit via dollibar
      
                  foreach($listproduct as $values) {
                      
                      if($values['barcode']!=""){
                          $data_list_product[$values['id']] = $values['barcode'];
                     
                      }
                      // tableau associatve entre ref et label product....
                  }
                    
                  // recupére les orders des données provenant de  woocomerce
                    // appel du service via api
                     $data_tiers = [];//data tiers dans dolibar
                     $data_lines  = [];// data article liée à commande du tiers en cours
                     $data_product =[]; // data article details sur commande facture
                     $data = [];
                     $lines =[]; // le details des articles produit achétés par le client
                     $id_commande_existe =[];// recupérer les id_commande existant deja récupérer dans les facture
                     $orders_d = [];// le nombre de orders non distributeur..
                     $orders_distributeur = [];// le nombre de orders des distributeurs...
                     $data_kdo =[] ; // recupérer les produit qui sont cadeaux 
                     $data_options_kdo =[];// données des kdo 
                     $data_infos_user =[];// pour gestion de kdo
                     $data_amount_kdo = [];// pour gestion kdo
                     $info_tiers_flush = [];// l'array qui va servir a flush dans ma base de données interne le nouveau client.
                     $data_echec = [];


                     // travailler sur le nommenclature de la ref facture
                      $date = date('Y-m-d');
                      $mm = date('m');
                      $jour = date('d');
                      $int_incr = 1;
                      $int_text ="00$int_incr";
                      $ref_ext ="WC-$jour$mm-$int_text";

                        foreach($orders as $k => $donnees) {
                                // créer des tiers pour dolibarr via les datas woocomerce. 
                                // créer le client via dolibarr à partir de woocomerce...
                                 $ref_client = rand(4,10);
                                 //  $email_true = mb_strtolower($donnees['billing']['email']);
                                 // recupérer id du tiers en fonction de son email...
                                 $email_true = mb_strtolower($donnees['billing']['email']);
                                 // recupérer id du tiers en fonction de son email...
                                  $fk_tiers = array_search($email_true,$data_list);
                                  $espace_phone =  str_replace(' ', '',$donnees['billing']['phone']);// suprimer les espace entre le phone
                              
                                   $fk_tiers_phone = array_search($espace_phone,$data_phone);
                                   // recupérer id en fonction du customer id
                                   // recupérer id en fonction du customer id
                                   $fk_tier = array_search($donnees['customer_id'],$data_code);
                                   // convertir la date en format timesamp de la facture .
                                    $datetime = $donnees['date']; // date recu de woocomerce.
                                    $date_recu = explode(' ',$datetime); // dolibar...
                                    // transformer la date en format date Y-m-d...
                                    $datex = $date_recu[0];
                                    $new_date = strtotime($datex);// convertir la date au format timesamp pour Api dolibarr.
                      
                             if($fk_tiers!=""){
                                $socid = $fk_tiers;
                             }

                              if($fk_tiers_phone !="" && $fk_tiers == ""){
                                $socid = $fk_tiers_phone;
                             }
                            
                             // construire le tableau
                             if($fk_tier!="" && $fk_tiers=="" && $fk_tiers_phone==""){
                               $socid = $fk_tier;
                                // recupérer dans la bdd en fonction du socid 
                            }

                            if($socid!=""){
                                 $data =  $this->tiers->gettiersid($socid);
                              if(count($data)==0){
                              $data_infos_user =[];
                              }else{

                                   foreach($data as $valu){
                                     $nom =$valu['nom'];
                                     $email = $valu['email'];
                                   }
                                   $data_infos_user = [
                                    'first_name'=> $nom,
                                    'last_name'=>'',
                                    'email'=>$email,
                                  ];
                            }

                          }




                     if($fk_tiers=="" && $fk_tier=="" && $fk_tiers_phone=="") {
                                   
                                    $date = date('Y-m-d');
                                    $dat = explode('-', $date);
                                    $a1 = $dat[0];
                                    // recupérer les deux deniers chiffre;
                                    $a11= substr($a1,-2);
                                    $a2 = $dat[1];
                                 
                                   $socid = $id_cl;
                                   $woo = $donnees['billing']['company'];
                                   
                                     $type_id="";
                                    $typent_code="";
                                    // defini si le client est un professionnel.
                                   if($woo!=""){
                                      $type_id ="235";
                                      $typent_code="PROF";
                                   }
                                   
                                   if(isset($donnees['is_professional'])){
                                   if($donnees['is_professional']==true){
                                     $type_id ="235";
                                     $typent_code="PROF";
                                   }

                                  }else{
                                    $type_id="";
                                    $typent_code="";
                                      
                                  }
                                   $name="";
                                   $code = $donnees['customer_id'];//customer_id dans woocomerce 
                                   $code_client ="WC-$a2$a11-$code";// créer le code client du tiers...

                                    // recupérer le prefix pays a partir du code client 
                                    $code_country = $donnees['billing']['country'];
                                    $id_country = array_search($code_country,$data_ids_country);
                                    if($id_country==""){
                                     $id_country=1;
                                     $code_country ="FR";
                                   }
                                   if($id_country!=""){
                                     $id_country = array_search($code_country,$data_ids_country);
                                     $code_country = $donnees['billing']['country'];
                                   }

                                   $data_tiers[] =[ 
                                   'entity' =>'1',
                                   'name'=> $donnees['billing']['first_name'].' '.$donnees['billing']['last_name'],
                                   'name_alias' => $woo,
                                   'address' => $donnees['billing']['address_1'],
                                   'zip' => $donnees['billing']['postcode'],
                                   'status'=>'1',
                                   'email' => $donnees['billing']['email'],
                                   "typent_id" => $type_id,
                                   "typent_code" => $typent_code,
                                   'phone' => $donnees['billing']['phone'],
                                    'client' 	=> '1',
                                    'code_client'	=> $code_client,
                                    'country_id' => $id_country,
                                    'country_code'=> $code_country
                                 ];
                                 
                                   $data_infos_user = [
                                        'first_name'=> $donnees['billing']['first_name'].' '.$donnees['billing']['last_name'],
                                        'last_name' =>'',
                                        'email'=>$donnees['billing']['email'],
                                     ];
                                    // recupérer un array pour créer un client via bdd base de données.
                                  $info_tiers_flush =[
                                     'name'=> $donnees['billing']['first_name'].' '.$donnees['billing']['last_name'],
                                     'socid'=> $socid,
                                     'code_client'	=> $code_client,
                                      'email' => $donnees['billing']['email'],
                                      'name_alias' => $woo,
                                      'address' => $donnees['billing']['address_1'],
                                      'city'=>  $donnees['billing']['city'],
                                      'zip' => $donnees['billing']['postcode'],
                                      'status'=>'1',
                                      'phone' => $donnees['billing']['phone'],
                                      'country_code'=> $donnees['billing']['country'],
                                      'date_created'=> date('Y-m-d H:i:s')
                                   ];
                              }

                              
                                foreach($donnees['line_items'] as $key => $values){

                                  foreach($values['meta_data'] as $val) {
                                     //verifié et recupérer id keys existant de l'article// a mettre à jour en vrai. pour les barcode
                                       if($val['value']!=null) {
                                          $fk_product = array_search($val['value'],$data_list_product); // fournir le barcode  de woocommerce  =  barcode  dolibar pour capter id product
                                       }
                                       else{
                                         $fk_product="";
                                      }
                                      $ref="";
                                     
                                      if($fk_product!=""){
                                             // recupérer les données du kdo 
                                            if($values['total'] == 0){
                                                 $data_kdo[] = [
                                                   "order_id" => $donnees['order_id'],
                                                   "product_id"=>$fk_product,
                                                   "label" =>$values['name'],
                                                   "quantity" => $values['quantity'],
                                                   "real_price"=> $values['real_price'],
                                                   "created_at" => date('Y-m-d h:i:s'),
                                                   "updated_at" => date('Y-m-d H:is')
                                                     ];
                                                      // recupérer les produit en kdo avec leur prix initial.
                                                 }
                                                 

                                               $tva_product = 20;
                                               $data_product[] = [
                                               "remise_percent"=> $donnees['discount_amount'],
                                               "multicurrency_subprice"=> floatval($values['subtotal']),
                                               "multicurrency_total_ht" => floatval($values['subtotal']),
                                               "multicurrency_total_tva" => floatval($values['total_tax']),
                                               "multicurrency_total_ttc" => floatval($values['total']+$values['total_tax']),
                                               "product_ref" => $ref, // reference du produit.(sku wwocommerce/ref produit dans facture invoice)
                                               "product_label" =>$values['name'],
                                               "qty" => $values['quantity'],
                                               "fk_product" => $fk_product,//  insert id product dans dolibar.
                                               "tva_tx" => floatval($tva_product),
                                                "ref_ext" => $socid, // simuler un champ pour socid pour identifié les produit du tiers dans la boucle /****** tres bon
                                        ];

                                     }

                                      if($fk_product=="") {
                                        // recupérer les les produits dont les barcode ne sont pas reconnu....
                                        $info = 'Numero de comande '.$donnees['order_id'].'';
                                        $data_echec[] = $values['name'].','.$info;
                                        $note =  'La facture est rejetée un produit n\'as pas un barcode lisible infos :'.$values['name'].' Numero commande :'.$donnees['id'].'';
                                        $ref_sku = $values['name'].','.$note;
                                        $list = new Transfertrefunded();
                                        $list->id_commande = $donnees['order_id'];
                                        $list->ref_sku = $note;
                                        $list->name_product = $values['name'];
                                        $list->quantite = $values['quantity'];
                                        $list->save();
                                     }
                                 }
                           }
                           
            
                               // verifier si la commande n'est pas encore traité..
                               $id_true ="";
                                if(isset($key_commande[$donnees['order_id']])==false) {
                                  
                                     // formalisés les valeurs de champs ajoutés id_commande et coupons de la commande.
                                    
                                      $data_options = [
                                       "options_idw"=>$donnees['order_id'],
                                       "options_idc"=>$coupons,
                                       "options_prepa" => $preparateur,
                                       "options_emba" => $emballeur,
                                       
                                       ];
                                      
                                      // liée la facture à l'utilisateur via un socid et le details des produits

                                        $data_lines[] = [
                                       'socid'=> $socid,
                                       'ref_client' =>$ref,
                                       'date'=> $new_date,
                                       "email" => $donnees['billing']['email'],
                                        "total_ht"  =>floatval($donnees['total_order']-$donnees['total_tax_order']),
                                        "total_tva" =>floatval($donnees['total_tax_order']),
                                        "total_ttc" =>floatval($donnees['total_order']),
                                        "paye"=>"1",
                                        "lines" =>$data_product,
                                        'array_options'=> $data_options,
                                        'linkedObjectsIds' => $linkedObjectsIds, // ajouter cette ligne si la facture d'une commande
                                    
                                      ];

                                        // Récupérer pour les cadeaux.
                                        $data_options_kdo = [
                                        "order_id"=>$donnees['order_id'],
                                        "coupons"=>$coupons,
                                        "total_order"=> floatval($donnees['total_order']),
                                        "date_order" => $donnees['date'],
                                       ];
                                      
                                        // recupérer le moyen de paiment dans la variable accountpay
                                        $this->setAccountpay($donnees['payment_method']);
                                        // recupérer le status si c'est un distributeur 
                                        if(isset($donnees['is_distributor'])){
                                            $status_distributeur = $donnees['is_distributor'];
                                        }
                                        else{
                                            $status_distributeur="no";
                                        }
                                        $this->setDistristatus($status_distributeur);

                                        $this->setFicfacture($donnees['order_id']);
                                        // insert dans base de donnees historiquesidcommandes
                                        $date = date('Y-m-d');
                                        $historique = new Commandeid();
                                        $historique->id_commande = $donnees['order_id'];
                                       $historique->date = $date;
                                        // insert to
                                       $historique->save();
                                      
                                   }
                                    else{
                                         $data_tiers=[];
                                         $info_tiers_flush =[];
                                         $data_kdo =[];// si le details est deja crée via un order_id.
                                         $data_infos_user =[];
                                         $data_options_kdo =[];
                                         $account="";
                                         $this->setAccountpay($account);
                                          echo json_encode(['success' => false, 'message'=> '  Attention la la commande semble etre deja facturée signalez au service informatique !']);
                                            exit;
                                    }
                                    // recupérer les id_commande deja pris
                                    if(isset($key_commande[$donnees['order_id']])==true) {
                                        // .....
                                        $id_commande_existe[] = $donnees['order_id'];
                                    }

                                  
                    
                      }

                           // filtrer les doublons du tableau..
                           $id_commande_exist = array_unique($id_commande_existe);
                          
                           // recupérer le tableau
                           $this->setDataidcommande($id_commande_exist);
                           // renvoyer un tableau unique par tiers via le socid...au cas de creation multiple de facture...
                         /* $temp = array_unique(array_column($data_lines, 'socid'));
                           $unique_arr = array_intersect_key($data_lines, $temp);
                          // trier les produits qui ne sont pas en kdo
                          foreach($unique_arr as $r => $val){
                           foreach($val['lines'] as $q => $vak) {
                             if($val['socid']!=$vak['ref_ext']){
                                unset($unique_arr[$r]['lines'][$q]); // filtrer le panier produit qui appartient uniquement que au user anec fonction de socid.
                             }
                           }
                         }
                        */
                         
                        // echo json_encode($data_lines);
                        // Create le client via Api...
                          foreach($data_tiers as $data) {
                          
                           // insérer les données tiers dans dolibar
                            $this->api->CallAPI("POST", $apiKey, $apiUrl."thirdparties", json_encode($data));
                          }
                    
                          foreach($data_lines as $donnes){
                          // insérer les details des données de la facture dans dolibarr
                          $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices", json_encode($donnes));
                        }
                     

                          // mettre la facture en status en payé et l'attribue un compte bancaire.
                            if(count($data_lines)!=0){
                               $this->invoicespay($orders);
                             }
                             // merger le client et les data coupons.....
                            $data_infos_order  = array_merge($data_infos_user,$data_options_kdo);
                            $tiers_exist = $this->don->gettiers();
                            // insert le tiers dans la BDD...
                           if(count($data_infos_order)!=0){
                              // insert 
                              if(isset($tiers_exist[$data_infos_order['email']])==false){
                                $this->don->inserts($data_infos_order['first_name'],$data_infos_order['last_name'],$data_infos_order['email'],$data_infos_order['order_id'],$data_infos_order['coupons'],$data_infos_order['total_order'],$data_infos_order['date_order']);
                               // JOINTRE les produits.
                         }
                      }
                          // Ajouter le client dans la base de données interne 
                          if(count($info_tiers_flush)!=0){
                             // 
                             $this->tiers->insert($info_tiers_flush['name'],$info_tiers_flush['name_alias'],$info_tiers_flush['socid'],$info_tiers_flush['code_client'],$info_tiers_flush['email'],$info_tiers_flush['phone'],$info_tiers_flush['address'],$info_tiers_flush['zip'],$info_tiers_flush['city'],$info_tiers_flush['date_created']);
                          }
                           // recupérer les cadeaux associé a l'utilisateur......
                          if(count($data_kdo)!=0){
                            $this->dons->inserts($data_kdo);
                         }

                         // Activer la facture en payé et attributer un moyen de paiement à la facture.
                        
                
        }
        
        public function invoicespay($orders)
        {
           
             $method = "GET";
             $apiKey = env('KEY_API_DOLIBAR'); 
             $apiUrl = env('KEY_API_URL');
             //appelle de la fonction  Api
              // $data = $this->api->getDatadolibar($apikey,$url);
             // domp affichage test 
              // recupérer le dernière id des facture 
              // recuperer dans un tableau les ref_client existant id.
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
                 'id_commande'=> $this->getFicfacture(),
                 'id_invoices'=>$inv

              ];

                  // Liee id de la commande au fk_facture et insert dans une table 
                  DB::table('fk_factures')->insert($data_fk_facture);
                  // le nombre de facture à traiter en payé
                  $count_data = count($ids_orders);
                  // les nouveau order à traiter
                   // recupérer le nombre de commande recupérer 
                 $nombre1 = $count_data;
                 $nombre2= count($this->getDataidcommande());// compter les anciennes ids 
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
                  $account_name = $this->getAccountpay();
                  // recupérer le status
                  $status_dist = $this->getDistristatus();

                 
                  if($account_name==""){
                    $account_name="vir_card";
                  }
                    // Moyens de paiments....id 4............
                    elseif($account_name=="stripe"){
                      // le mode de reglement !!
                      $mode_reglement_id=107; // prod.....
                   }

                   elseif($account_name=="payplug"){
                      // le mode de paiment.
                       $mode_reglement_id =106;// prod.....
                   }
                   
                   elseif($account_name=="apple_pay"){
                        $mode_reglement_id =6;
                   }
                   
                    elseif($account_name=="bancontact"){
                        $mode_reglement_id =6;
                   }

                   elseif($account_name=="cod"){
                      $mode_reglement_id =6;
                    }

                   elseif($account_name=="CB"){
                      $mode_reglement_id =6;
                   }
                   
                    elseif($account_name=="oney_x4_with_fees"){
                      $mode_reglement_id=108; // payplug 4x..
                   }
                    elseif($account_name=="bacs"){
                      $mode_reglement_id=3; // ordre de prelevement......
                   }

                   elseif($account_name=="gift_card"){
                       $mode_reglement_id = 57;
                   }

                   elseif($account_name=="DONS"){
                      $mode_reglement_id = 57;
                  }

                   else{
                       $mode_reglement_id=3;
                   }

                  
                   $array_paiment = array('cod','vir_card1','vir_card','payplug','stripe','oney_x3_with_fees','oney_x4_with_fees','apple_pay','american_express','gift_card','bancontact','CB');// carte bancaire....
                   $array_paiments = array('bacs', 'VIR');// virement bancaire id.....
                   $array_paimentss = array('DONS');

                   if(in_array($account_name,$array_paiment)) {
                    // defini le mode de paiment commme une carte bancaire...
                     //$mode_reglement_id = 6;
                       $account_id=4;// PROD 
                       $paimentid =4;// PROD
                   }

                   if(in_array($account_name,$array_paiments)){
                      // defini le paiment comme virement bancaire......
                       //$mode_reglement_id = 4;
                       $account_id=6; // PROD
                       $paimentid =6;// PROD
                    }

                    if(in_array($account_name,$array_paimentss)){
                        // dons 
                         $account_id=3; // PROD
                         $paimentid =3;// PROD
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

                      if($status_dist!="true"){
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
                  $datetime = date('d-m-Y H:i:s');
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
                "paymentid"=>6,
                "closepaidinvoices"=> "yes",
                "accountid"=> $account_id, // id du compte bancaire.
               ];

              // valider les facture dans dolibar....
               if($valid==1){
                 // valider la facture en impayée.
                  $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$inv."/validate", json_encode($newCommandeValider));
               }
               else{
                     // valider et mettre en payée la facture.
                     $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$inv."/validate", json_encode($newCommandeValider));
                     // Lier les factures dolibar  à un moyen de paiement et bank.
                     $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$inv."/payments", json_encode($newbank));
                    // mettre le statut en payé dans la facture  dolibar
                    $this->api->CallAPI("PUT", $apiKey, $apiUrl."invoices/".$inv, json_encode($newCommandepaye));
              }

        }


         public function Updatefacture($orders){
            
           // connexion api dolibar
             $method = "GET";
            $apiKey = "f2HAnva64Zf9MzY081Xw8y18rsVVMXaQ"; 
            $apiUrl = "https://www.transfertx.elyamaje.com/api/index.php/";

            // traiter le jeu de tableau
            // recupérer
            $datas= $this->commande->getIdsfkfacture();
           // recupération des moyens de paiment
           $moyen_card = $this->commande->createpaiementid();
           // recuper les fk_facture et reconstuire le tableau envoyé
           $test_data =[];
           $data_fk_facture =[];
           // construire le tableau pour les montant de chaque paimement de commande.
           $newCommandepaye =[];
           $newbank =[];// attributeur un compte id de paiement
           $newCommandeValider =[];// valider les facture en cas du valid=1(mettre en impayes pour ditributeur);
           // le tableau pour valider les facture sur l'entrepot preics.
           // traiter les moyens de paimen
            // crée laccount paiement à partir de la methode de paiment.
            $array_paiment = array('cod','vir_card1','vir_card','payplug','stripe','oney_x3_with_fees','oney_x4_with_fees','apple_pay','american_express','gift_card','bancontact','CB');// carte bancaire....
            $array_paiments = array('bacs', 'VIR');// virement bancaire id.....
            $array_paimentss = array('DONS');
            $valid="";
           foreach($orders as $values){
                // recupérer le fk_facture.
                $fk_facture = array_search($values['order_woocommerce_id'],$datas);
                // recupérer le moyen de payament
                $moyen_paid =  array_search($values['payment_method'],$moyen_card);
                $moyen_paids = explode(',',$moyen_paid);
                // recupérer le status de la commande(cas de distributeur);
                 $status_distributeur = $values['is_distributor'];
                 if($status_distributeur==""){
                    $valid=0;
                 }

                 if($status_distributeur=="true" && $values['payment_method']=="bacs"){
                    $valid=1;
                 }

                 if($status_distributeur!="true"){
                    $valid=3;
                  }

                 // moyen de paiement.
                 if($moyen_paids!=false){
                     $mode_reglement_id = $moyen_paids[0];
                     $moyen_paiement = $values['payment_method'];
                 }
                 
                 if($moyen_paids==false){
                     $moyen_paiement = "vir_card";
                     $mode_reglement_id=3;
                 }

                 // attribuer le compte de paiment ensuite.
                 if(in_array($moyen_paiement,$array_paiment)) {
                   // defini le mode de paiment commme une carte bancaire...
                  //$mode_reglement_id = 6;
                   $account_id=4;// PROD 
                   $paimentid =4;// PROD
               }

                if(in_array($moyen_paiement,$array_paiments)){
                  // defini le paiment comme virement bancaire......
                   //$mode_reglement_id = 4;
                    $account_id=6; // PROD
                    $paimentid =6;// PROD
                }

                 if(in_array($moyen_paiement,$array_paimentss)){
                    // dons 
                     $account_id=3; // PROD
                      $paimentid =3;// PROD
                  }

                   $data_fk_facture[]= $fk_facture;// recupérer les id de facture depuis dolibar.
                   foreach($values['line_items'] as $val){
                    $chaine = $val['quantity'].','.$val['subtotal'].','.$val['meta_data'][0]['value'];
                    $test_data[$chaine] = $val['meta_data'][0]['value'].','.$fk_facture;
                  }

                   // array pour paimement de la facture.
                    $newCommandepaye[$values['order_woocommerce_id'].','.$valid.','.$fk_facture] = [
                    "total_ht"  =>$values['total_order']-$values['total_tax_order'],
                    "total_tva" =>$values['total_tax_order'],
                    "total_ttc" =>$values['total_order'],
                     "paye"	=> 1,
                     "statut"	=> 2,
                     "mode_reglement_id"=>$mode_reglement_id,
                     "idwarehouse"=>6,
                     "notrigger"=>0,
                 ];

                  // attribuer un array pour le compte bancaire de la facture
                  $datetime = date('d-m-Y H:i:s');
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
    
                    $newbank[$values['order_woocommerce_id'].','.$valid.','.$fk_facture] = [
                     "datepaye"=>$date_finale,
                     "paymentid"=>6,
                     "closepaidinvoices"=> "yes",
                     "accountid"=> $account_id, // id du compte bancaire.
                ];

                 // tableau pour valider les  factures
                  $newCommandeValider[$values['order_woocommerce_id'].','.$valid.','.$fk_facture] = [
                  "idwarehouse"	=> "6",
                   "notrigger" => "0",
                  ];
 
           }

          
           // aller chercher les correspondances lines associé à ces factures dans dolibar pour line product.
             foreach($data_fk_facture as $vc){
              $json_data[] = json_decode($this->api->CallAPI("GET", $apiKey, $apiUrl."invoices/".$vc),true);
            }
      
            // recupérer les prdoduct avec leur barcode pour utiliser plutard(important)
            $produitParam = ["limit" => 1600, "sortfield" => "rowid"];
            $listproduct = $this->api->CallAPI("GET", $apiKey, $apiUrl."products", $produitParam);
            // reference ref_client dans dolibar
            $listproduct = json_decode($listproduct, true);// la liste des produits dans doliba.
            $data_list_product =[];
            foreach($listproduct as $values) {
                   if($values['barcode']!=""){
                   $data_list_product[$values['barcode']] = $values['id'];
              }
              // tableau associatve entre ref et label product....
           }

            foreach($json_data as  $key => $valus){
               foreach($valus['lines'] as $va){
                 // renvoyer les bon prix à partir du barcode 
                   $data_result[$va['fk_facture'].','.$va['rowid']][] =[
                       "barcode"=>array_search($va['fk_product'],$data_list_product).','.$va['fk_facture'],
                       "multicurrency_subprice"=> $va['multicurrency_subprice'],
                       "multicurrency_total_ht"=> $va['multicurrency_subprice'],
                       "qty"=>$va['qty'],
                        "tva_tx"=>$va['tva_tx'],
                     ];

                }
             }
             // construire un jeu de données pour recupérer les prix provenant de la commande woocomerce
             foreach($data_result as $lm => $val){
               foreach($val as $valis){
                $chaine_data = array_search($valis['barcode'],$test_data);
                if($chaine_data!=false){
                    $donnees = explode(',',$chaine_data);
                    $result_finale[$lm] =[
                     "multicurrency_subprice"=> $donnees[1],
                     "multicurrency_total_ht"=> $donnees[1],
                      "qty"=>$donnees[0],
                      "tva_tx"=>20,
                  ];

               }
            }
           }
            
            dd($data_fk_facture);
             // Mettre les facture en brouillons et suprimer le compte lié
               $data_fact =[
                "idwarehouse"=>"6"
               ];
               foreach($data_fk_facture as $valu){
                  $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$valu."/settounpaid",json_encode($data_fact));
                  $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$valu."/settodraft");

               }

                // detruire dans la table lyq_facture_paiement les paiements associé à la facture.
                // ici.
                $list_fk_facture = implode(',',$data_fk_facture);
               // $deletepaiement  = DB::connection('mysql2')->select("DELETE FROM llxyq_paiement_facture WHERE IN");

                 dd('zapo');
          
                 // Mise à jours des ligne de product en masse(prix , quantité)
                  foreach($result_finale as $kyes => $valus){
                    $ids_facture  = explode(',',$kyes);
                      // mettre à jours les factures 
                       $this->api->CallAPI("PUT", $apiKey, $apiUrl."invoices/".$ids_facture[0]."/lines/".$ids_facture[1]."",json_encode($valus));
                       
                     }  
                      // mettre la facture en validé et  attributeur un  moyen de paimement
                       foreach($newCommandeValider as $ks => $val){
                                $chaine_reel = explode(',',$ks);
                                $inv = $chaine_reel[2];
                                  if($chaine_reel[1]=="1"){
                                    // valider la facture et la mettre en impayés c'est un distributeur
                                    $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$inv."/validate", json_encode($val));
                                 }

                                 $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$inv."/validate", json_encode($val));
                             
                             }
                                
                               foreach($newbank as $kj => $vl){
                                      $chaine_reels = explode(',',$kj);
                                       $ins = $chaine_reels[2];
                                      if($chaine_reels[1]!=1){
                                        $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$ins."/payments", json_encode($vl));
                                    }
                                }
                                   // attributeur les nouveaux montant et mettre les facture en payés + le moyen de paimement .
                                   foreach($newCommandepaye as $km => $valo){
                                       $chaine_rees = explode(',',$km);
                                        $inc = $chaine_rees[2];
                                        if($chaine_rees[1]!=1){
                                          $this->api->CallAPI("PUT", $apiKey, $apiUrl."invoices/".$inc, json_encode($valo));
                                      }
                                }

               dd('succees');
         }

  }


