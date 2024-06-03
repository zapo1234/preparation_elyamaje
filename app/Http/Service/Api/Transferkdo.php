<?php
namespace App\Http\Service\Api;

use DateTime;
use DateTimeZone;
use App\Models\Don;
use App\Models\Commandeid;
use App\Models\Productdiff;
use App\Models\Transfertsucce;
use App\Models\Transfertrefunded;
use Automattic\WooCommerce\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Repository\Don\DonRepository;
use App\Repository\Tiers\TiersRepository;
use App\Repository\Don\DonsproductRepository;
use App\Repository\LogError\LogErrorRepository;
use App\Models\Distributeur\Invoicesdistributeur;
use App\Repository\Commandeids\CommandeidsRepository;
use Automattique\WooCommerce\HttpClient\HttpClientException;

class Transferkdo
{
    
      private $api;
      
      private $commande;
      private $dataidcommande;// recupérer les ids commande existant
      private $status; // vartiable string pour statuts(customer et distributeur)
      private $countd = []; // les clients distributeur
      private $countc = [];// les clients non distributeur.
      private $idcommande =[];// recupérer les commandes....
      private $accountpay;
      private $distristatus;
      private $ficfacture;
      private $logError;
      private $don;
      private $dons;
      private $tiers;
      private $idaccount;
    
       public function __construct(
        Api $api,
        CommandeidsRepository $commande,
        TiersRepository $tiers,
        DonRepository $don,
        DonsproductRepository $dons,
        LogErrorRepository $logError
       )
       {
         $this->api=$api;
         $this->commande = $commande;
         $this->tiers = $tiers;
         $this->don = $don;
         $this->dons = $dons;
         $this->logError = $logError;
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


   public function getIdcommande(): array
   {
         return $this->idcommande;

   }

   public function setIdcommande(array $idcommande)
   {
       $this->idcommande = $idcommande;
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


   public function getIdaccount()
   {
       return $this->idaccount;
   }
   
   
   public function setIdaccount($idaccount)
   {
      $this->idaccount = $idaccount;
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
      public function Transferkdo($orders)
      {
            
           
             $fk_commande="";
             $linkedObjectsIds =[];
             $coupons="";
             $emballeur="";
             $preparateur="";
             $gift_card_amount="";// presence de dépense avec gift_card.
             $id_compte="";
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
                  
                 if($val['gift_card_amount']!=""){
                      $gift_card_amount = $val['gift_card_amount'];
                 }

                
             }

             
             
                 $method = "GET";
                 // recupérer les clé Api dolibar transfertx........
                 $apiKey = env('KEY_API_DOLIBAR'); 
                 $apiUrl = env('KEY_API_URL');

                 $produitParam = ["limit" => 1600, "sortfield" => "rowid"];
	               $listproduct = $this->api->CallAPI("GET", $apiKey, $apiUrl."products", $produitParam);
                 // reference ref_client dans dolibar
                   $listproduct = json_decode($listproduct, true);// la liste des produits dans dolibarr


                  if(count($listproduct)==0){
                    $this->logError->insert(['order_id' => isset($orders[0]['order_woocommerce_id']) ? $orders[0]['order_woocommerce_id'] :  0, 'message' => 'la facture n\'a pas été crée signalé au service informatique !']);
                    
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
                     $data_gift_card =[];// data liee au commande des gift_card acheté.
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
                     $reel_indice="";// qui identifie la création d'une facture quand on est a 1;
                     $fact_to ="";

                     // travailler sur le nommenclature de la ref facture
                      $date = date('Y-m-d');
                      $mm = date('m');
                      $jour = date('d');
                      $int_incr = 1;
                      $int_text ="00$int_incr";
                      $ref_ext ="WC-$jour$mm-$int_text";
                      
                       // preparation des données gift_card pour les cartes cadeaux.
                        $array_data_gift_card =[];
                        $montant_carte_kdo = [];
                        $array_commande_data =[];// recupérer les commande deja facturé.
                        
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
                                 
                                    $socid = $id_cl++;
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
                                   
                              }

                                // recupére des lines pour des gift card
                                foreach($donnees['line_items'] as $key => $values){
                                   // traiter le cas des cartes cadeaux..

                                     foreach($values['meta_data'] as $val) {
                                      
                                      if($val['key']=="barcode"){
                                         //verifié et recupérer id keys existant de l'article// a mettre à jour en vrai. pour les barcode
                                            $fk_product = array_search($val['value'],$data_list_product); // fournir le barcode  de woocommerce  =  barcode  dolibar pour capter id product
                                            $product_type ="";
                                            $desc="";
                                            $product_label= $values['name'];
                                       }
                                       else{
                                             $fk_product="gala";
                                             $product_type="1";
                                             $desc="Achat billet gala Septmebre 2024";
                                             $product_label="";
                                        }

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
                                                 

                                               $tva_product = 0;
                                               $data_product[] = [
                                                "desc"=>$desc,
                                                "remise_percent"=> $donnees['discount_amount'],
                                                "multicurrency_subprice"=> floatval($values['subtotal']),
                                                "multicurrency_total_ht" => floatval($values['subtotal']),
                                                "multicurrency_total_tva" => 0,
                                                "multicurrency_total_ttc" => floatval($values['total']),
                                                "product_ref" => $ref, // reference du produit.(sku wwocommerce/ref produit dans facture invoice)
                                                "product_label" =>$product_label,
                                                "qty" => $values['quantity'],
                                                "fk_product" => $fk_product,//  insert id product dans dolibar.
                                                "tva_tx" => "",
                                                "ref_ext" => $socid, // simuler un champ pour socid pour identifié les produit du tiers dans la boucle /****** tres bon
                                        ];

                                           // recupérer la methode shipping_method_name
                                           
                                            $chaine_name_shipping = $donnees['shipping_method_detail'];
                                            
                                           
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
                                 
                                  // verifier si la commande n'est pas encore traité..
                                  $id_true ="";
                                  $fid=1;
                                  if(isset($key_commande[$donnees['order_id']])==false) {

                                    if($donnees['gala']=="true"){
                                       $index_number="-gala";
                                    }else{
                                        $index_number ="-cado";
                                    }
                                  
                                      if($donnees['gala']==true){
                                        $index_numero ='-gala';
                                      }else{
                                          $index_numero = '-cado';
                                      }
                                    
                                      $data_options = [
                                        "options_idw"=>$donnees['order_id'].''.$index_number,
                                        "options_idc"=>$coupons,
                                        "options_fid"=>$fid,
                                        "options_prepa" => $preparateur,
                                        "options_emba" => $emballeur,
                                        "options_point_fidelite"=>0,
                                        ];
                                      
                                       // liée la facture à l'utilisateur via un socid et le details des produits
                                       // data normale de la facture sans bon cadeaux ou achat via bon gift cart.
                                       
                                          $data_lines[] = [
                                          'socid'=> $socid,
                                          'ref_client' =>$donnees['order_id'],
                                          'date'=> $new_date,
                                          "email" => $donnees['billing']['email'],
                                          "total_ht"  =>floatval($donnees['total_order']-$donnees['total_tax_order']),
                                          "total_tva" =>0,
                                          "total_ttc" =>floatval($donnees['total_order']),
                                          "paye"=>"1",
                                          "lines" =>$data_product,
                                          'array_options'=> $data_options,
                                          'linkedObjectsIds' => $linkedObjectsIds, // ajouter cette ligne si la facture d'une commande
                                    
                                       ];

                                        
                                        $this->setFicfacture($donnees['order_id']);
                                        // insert dans base de donnees historiquesidcommandes
                                        $date = date('Y-m-d');
                                        $historique = new Commandeid();
                                        $historique->id_commande = $donnees['order_id'];
                                        $historique->date = $date;
                                        // insert to
                                        $historique->save();
                                      
                                   }
                                      // recupérer les id_commande deja pris
                                      if(isset($key_commande[$donnees['order_id']])==true) {
                                        // .....
                                        $id_commande_existe[] = $donnees['order_id'];
                                    }

                                  
                           }


                        }


                         if(count($data_lines)!=0){
                              // renvoyer un tableau unique par tiers via le socid...au cas de creation multiple de facture...
                          $temp = array_unique(array_column($data_lines, 'socid'));
                           $unique_arr = array_intersect_key($data_lines, $temp);
                          // trier les produits qui ne sont pas en kdo
                          foreach($unique_arr as $r => $val){
                           foreach($val['lines'] as $q => $vak) {
                             if($val['socid']!=$vak['ref_ext']){
                                unset($unique_arr[$r]['lines'][$q]); // filtrer le panier produit qui appartient uniquement que au user anec fonction de socid.
                             }
                           }
                         }
                        }
                         else{
                               $message ="Aucune datas de facture recupérer";
                               echo json_encode(['success' => false, 'message'=> $message]);
                               exit;
                            }
                      
                          
                          // Create le client via Api.....
                        // fitrer les ids de commande .qui sont deja facture et les enlever.
                          $array_tab = []; // au recupere les socid
                          $ids_commande =[];// recupérer les ids dans commande.
                        
                         if(count($unique_arr)!=0){

                            foreach($unique_arr as $key =>$van){
                                // if le tableau des lines est vide ne pas considére
                                 if(count($van['lines'])==0){
                                      $array_tab[] = $key;
                                      
                                  }
                              }
                            // les retirer du tableau.
                             foreach($array_tab as $clef=>$val){
                                unset($unique_arr[$clef]);

                              }
                               
                            

                              foreach($data_tiers as $data) {
                                // insérer les données tiers dans dolibar
                                $retour_create =  $this->api->CallAPI("POST", $apiKey, $apiUrl."thirdparties", json_encode($data));
                             
                               }

                               $retour_create_facture="";// gerer le retour de la création api.
                             foreach($unique_arr as $donnes){
                              // insérer les details des données de la facture dans dolibarr
                               $ids_commande[] = $donnes['ref_client'];
                               $retour_create = $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices", json_encode($donnes));
                             }
                            // traiter la réponse de l'api
                            //  $response = json_decode($retour_create, true);

                             // recupérer les commande a facture
                             $this->setIdcommande($ids_commande);

                             // mettre la facture en status en payé et l'attribue un compte bancaire.
                             if(count($unique_arr)!=0){
                                 $this->invoicespay($orders);
                             }
                           // Activer la facture en payé et attributer un moyen de paiement à la facture.
                       }else{
                            $message ="Aucune datas de facture recupérer";
                            echo json_encode(['success' => false, 'message'=> $message]);
                            exit;
                       }
                
        }
        
        public function invoicespay($orders)
        {
             // recuperer les données api dolibar.
             // recuperer les données api dolibar copie projet tranfer x.
      
             $id_account="";
             foreach($orders as $vn){
              if($vn['gala']=="true"){
                  $id_account = 48;// gala
               }else{
                  $id_account= 46; // kado
               }
              }

             $this->setIdaccount($id_account);
             
             $method = "GET";
             // recupérer les clé Api dolibar transfertx........
             $apiKey = env('KEY_API_DOLIBAR'); 
             $apiUrl = env('KEY_API_URL');

             // recupérer le dernière id des facture 
             // recuperer dans un tableau les ref_client existant id.
             $invoices_id = json_decode($this->api->CallAPI("GET", $apiKey, $apiUrl."invoices", array(
	    	     "sortfield" => "t.rowid", 
	    	      "sortorder" => "DESC", 
		          "limit" => "1", 
		          "mode" => "1",
	        	)
           	), true);
            
           // recupération du dernier id invoices dolibar
            foreach($invoices_id as $vk) {
              $inv = $vk['id'];
           }

            // le nombre de facture à traiter en payé
            $count_data = count($orders);
           // les nouveau order à traiter
           // recupérer le nombre de commande recupérer 
           $nombre1 = $count_data;
         
           
             $nombre_count = $inv - $count_data+1;
             $nombre_orders = $nombre_count;
             $datetime = date('d-m-Y H:i:s');
             $dat = date('Y-m-d H:i:s');
         
           // insert infos dans bdd ...
           if($nombre_orders == 0) {
             $label = "Aucune commande transférée";
         }
          elseif($nombre_orders ==1) {
             $label ="Une commande transférée dans dolibars le $datetime";
         }
          else{
               $label = "$nombre_orders commandes transférées dans dolibars le $datetime";
         }
         
        // insert dans la table 
          $sucess = new Transfertsucce();
          $sucess->date = $dat;
           $sucess->id_commande = '';
           $sucess->label = $label;
           $sucess->save();
     
      
          // valider invoice
          $newCommandeValider = [
          "idwarehouse"	=> "6",
          "notrigger" => "0",
          ];
    
          
           $newCommandepaye = [
            "paye"	=> 1,
            "statut"	=> 2,
            "mode_reglement_id"=>6,
            "idwarehouse"=>6,
            "notrigger"=>0,
      
         ];
        
   
         // recupérer la datetime et la convertir timestamp
         // liée la facture à un mode de rélgement
         // convertir la date en datetime en timestamp.
            $datetime = date('d-m-Y H:i:s');
            $d = DateTime::createFromFormat(
           'd-m-Y H:i:s',
           $datetime,
           new DateTimeZone('UTC')
           );
     
        if ($d === false) {
         die("Incorrect date string");
      } else {
           $date_finale =  $d->getTimestamp(); // conversion de date.
        }
      
            $newbank = [
           "datepaye"=>$date_finale,
           "paymentid"=>6,
           "closepaidinvoices"=> "yes",
           "accountid"=> $this->getIdaccount(), // id du compte bancaire.
           ];

            // contruire le tableau newbank
            $ord = $this->getIdcommande();
            $array_keys = [];// recupérer les cles du tableau.
            $new_banks =[];// reucpérer 
        
           foreach($ord as $ks =>$vb){
               $array_keys[] = $ks;
           }

             for($i=$nombre_count; $i<$inv+1; $i++){
                  $new_bank[]=
                             [$i =>[
                                "datepaye"=>$date_finale,
                               "paymentid"=>6,
                               "closepaidinvoices"=> "yes",
                                "accountid"=>$this->getIdaccount(),// id du compte bancaire. 

                           ]
                       ];
               }

              // forunir l'id de chaque array pour le fixer sur la ligne de d'ecritures
                  foreach($new_bank as $keys=> $values){
                       if(in_array($keys,$array_keys)){
                             foreach($values as $ky => $vk){
                              $new_banks[]=[$ky=>[
                                    "datepaye"=>$vk['datepaye'],
                                    "paymentid"=>6,
                                    "closepaidinvoices"=> "yes",
                                     "accountid"=>$this->getIdaccount(),// id du compte bancaire..
                                     "num_payment"=>$ord[$keys]
                                   ]
                             ];
                        }
                   }
               }
             
             
           // valider les facture dans dolibar
           for($i=$nombre_count; $i<$inv+2; $i++) {
              $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$i."/validate", json_encode($newCommandeValider));
           }
      
             // Lier les factures dolibar  à un moyen de paiement et bank.
           foreach($new_banks as $vals){
                 foreach($vals as $km =>$vas){
                     $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$km."/payments", json_encode($vas));
                  }
           }

              // mettre le statut en payé dans la facture  dolibar
           for($i=$nombre_count; $i<$inv+2; $i++){
             $this->api->CallAPI("PUT", $apiKey, $apiUrl."invoices/".$i, json_encode($newCommandepaye));
           }

     }

}
