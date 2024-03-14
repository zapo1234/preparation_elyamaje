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

class Transfertext
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
      private $don;
      private $dons;
      private $tiers;
      private $amountcard;
     
    
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


   public function getAmountcard()
   {
       return $this->amountcard;
   }
   
   
   public function setAmountcard($amountcard)
   {
      $this->amountcard = $amountcard;
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

    public function getnameshippingmethod(){
          
        $array_name =[
          '1,Colissimo,Colissiomo avec signature'=>'Colissimo',
          '2,Chronopost,Chronopost avec '=>'Chronopost',
          '3,Retraitdistributeur,Retrait distributeur'=>'Retraitdistributeur',
          '4,Other,Autre méthode'=>'Other'
        ];

        return $array_name;
    }
     
     /** 
     *@return array
     */
      public function Transfertext($orders)
      {
           
            $fk_commande="";
             $linkedObjectsIds =[];
             $coupons="";
             $emballeur="";
             $preparateur="";
             $gift_card_amount="";// presence de dépense avec gift_card.
             foreach($orders as $val){
                 if(isset($val['fk_commande'])){
                    $id_commande="exist";
                    $linkedObjectsIds =  ["commande" => [""=>$val['fk_commande']]];
                    $emballeur = $val['emballeur'];
                    $preparateur= $val['preparateur'];
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

                 // voir si le montant amouncard existe 
                 if(isset($val['amountCard'])){
                       $amount_card = $val['amountCard'];
                       $total_montant = $val['total'];
                       $indice_amount_liq ="";
                       if($total_montant == $amount_card){
                            $indice_amount_liq=0;//100% paiement par card.
                        }else{

                          if($amount_card!=0){
                              // si y'a eu paimement espece et card cardeau
                               $indice_amount_liq =  $total_montant - $amount_card;
                          }

                          if($amount_card==0){
                              // y'a paiement en espece uniquement.
                              $indice_amount_liq="liqpaid";
                          }
                     }

                       $chaine_amount = "$amount_card%$indice_amount_liq"; // lier le cas des en fonction du retour chaine.....

                 }else{
                      $amount_card=0;
                      $chaine_amount ='0%nobpp';
                 }
             }
             
               // recupérer le montant payé par cartependant la BP...

               $this->setAmountcard($chaine_amount);
             
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
                    // appel du service via api.....
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
                        $gift_card="";
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
                                 
                                   //$socid = $id_cl;
                                   $socid="news";
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

                                   $chaine_index ="BPP";
                                   if(strpos($donnees['order_id'],$chaine_index)!==false){
                                     $code_client = $donnees['order_id'];
                                   }else{
                                     $code = $donnees['customer_id'];
                                     $code_client ="WC-$a2$a11-$code";// créer le code client du tiers...
                                  }
                                 

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
                                   
                                   // create tiers news
                                   $data_tiers[] =[ 
                                   'entity' =>'1',
                                   'name'=> $donnees['billing']['first_name'].' '.$donnees['billing']['last_name'],
                                   'name_alias' => $woo,
                                   'address' => $donnees['billing']['address_1'],
                                   'zip' => $donnees['billing']['postcode'],
                                   'town'=> $donnees['billing']['city'],
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


                                // recupére des lines pour des gift card
                                foreach($donnees['line_items'] as $key => $values){
                                   // traiter le cas des cartes cadeaux..

                                     foreach($values['meta_data'] as $val) {

                                     //verifié et recupérer id keys existant de l'article// a mettre à jour en vrai. pour les barcode
                                       if($val['value']!=null) {
                                          $fk_product = array_search($val['value'], $data_list_product); // fournir le barcode  de woocommerce  =  barcode  dolibar pour capter id product
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
                                                "desc"=>'',
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

                                           // recupérer la methode shipping_method_name
                                           
                                            $chaine_name_shipping = $donnees['shipping_method_detail'];
                                            
                                            /*$shipping_true = str_replace(' ', '', $chaine_name_shipping);
                                            dump($shipping_true);
                                            $array_shipping = $this->getnameshippingmethod();
                                            dump($array_shipping);
                                            $result = array_search($shipping_true,$array_shipping);
                                            $result_s = explode(',',$result);
                                            $indice_ship = $result_s[1];
                                            dd($result_s[2]);

                                          */
                                            // transformer en minuscule les valeur qui arrive
                                            
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
                                 
                                     // gérer les moyens de transport de collisimo
                                       $array_line_product =[];
                                       $total_a_tva = $donnees['shipping_amount']*20/100;
                                       if($chaine_name_shipping!=""){
                                          $array_line_product[]=[
                                           "desc"=>$chaine_name_shipping,
                                           "multicurrency_subprice"=> floatval($donnees['shipping_amount']),
                                           "multicurrency_total_ht" => floatval($donnees['shipping_amount']),
                                           "multicurrency_total_tva" => floatval($total_a_tva),
                                           "multicurrency_total_ttc" => floatval($donnees['shipping_amount']+$total_a_tva),
                                           "product_ref" =>'', // reference du produit.(sku wwocommerce/ref produit dans facture invoice)
                                           "product_type"=>'1',
                                           "product_label" =>'',
                                            "qty" => '1',
                                            "fk_product" =>'',//  insert id product dans dolibar.
                                            "tva_tx" => floatval($tva_product),
                                            "ref_ext" => $socid, // simuler un champ pour socid pour identifié les produit du tiers dans la boucle /****** tres bon
                                         ];
                                        }
                                     
                                    $result_data_product = array_merge($array_line_product,$data_product);
                                  // verifier si la commande n'est pas encore traité..
                                  $id_true ="";
                                  if(isset($key_commande[$donnees['order_id']])==false) {
                                  
                                      // formalisés les valeurs de champs ajoutés id_commande et coupons de la commande.
                                      // veifier si la commande a facturé vient d'une beauty proof BPP
                                      
                                       $chaine_ext ="BPP";
                                       $index_int="";// eviter que les commande de la BPP sois prise en compte.
                                       if(strpos($donnees['order_id'],$chaine_ext)!==false){
                                            $index_int=1;
                                            $montant_fidelite = 0.000;
                                       }else{
                                             $index_int="";
                                             $total_shipping = $donnees['shipping_amount']*1.2;
                                             $montant_fidelite = $donnees['total_order']-$total_shipping+$donnees['gift_card_amount'];
                                             
                                       }

                                      $data_options = [
                                       "options_idw"=>$donnees['order_id'],
                                       "options_idc"=>$coupons,
                                       "options_fid"=>$index_int,
                                       "options_prepa" => $preparateur,
                                       "options_emba" => $emballeur,
                                       "options_point_fidelite"=>$montant_fidelite,
                                        ];
                                      
                                       // liée la facture à l'utilisateur via un socid et le details des produits
                                       // data normale de la facture sans bon cadeaux ou achat via bon gift cart.
                                        $data_lines[] = [
                                        'socid'=> $socid,
                                        'ref_client' =>$ref,
                                        'date'=> $new_date,
                                        "email" => $donnees['billing']['email'],
                                        "total_ht"  =>floatval($donnees['total_order']-$donnees['total_tax_order']),
                                        "total_tva" =>floatval($donnees['total_tax_order']),
                                        "total_ttc" =>floatval($donnees['total_order']),
                                        "paye"=>"1",
                                        "lines" =>$result_data_product,
                                        'array_options'=> $data_options,
                                        'linkedObjectsIds' => $linkedObjectsIds, // ajouter cette ligne si la facture d'une commande
                                    
                                      ];

                                      // tableau de construction des facture de gift_cart lorqu'elle sont détecter.
                                      // créer les facture pour le gift cart.
                                       $ext_traitement = 0;
                                      
                                       // construire mon tableau de ma seconde facture au cas il existe des bon d'achat gift_card ou des cadeaux line
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

                                        // recupérer le montant
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
                                          echo json_encode(['success' => false, 'message'=> '  Attention la commande semble être déjà facturée, signalez au service informatique !']);
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
                        
                          
                         // Create le client via Api.....
                           
                          if(count($data_tiers)!=0){
                             foreach($data_tiers as $data) {
                           // insérer les données tiers dans dolibar
                             $retour_create_tiers =  $this->api->CallAPI("POST", $apiKey, $apiUrl."thirdparties", json_encode($data));
                                if($retour_create_tiers==""){
                                   $message ="Problème sur la création du client";
                                   $this->logError->insert(['order_id' => isset($orders[0]['order_woocommerce_id']) ? $orders[0]['order_woocommerce_id'] :  0, 'message' => $message]);
                                   echo json_encode(['success' => false, 'message'=> $message]);
                                   exit;
                              }
                           }
                          }
                          //
                          // recrire la data_lines.
                          if($data_lines[0]['socid']=="news"){
                              $data_lines[0]['socid']=$retour_create_tiers;
                          }else{
                              $data_lines= $data_lines;
                          }

                          // traiter les commande achété avec des bon d'achat ici.
                          
                  
                          foreach($data_lines[0]['lines'] as $keys => $val){
                            $chainex ="Carte";
                            $chainex1 ="Cadeau";
                             if(strpos($val['product_label'],$chainex)!=false && strpos($val['product_label'],$chainex1)!=false){
                                 if($val['product_label']!=""){
                                   $chaine_index = explode(' ',$val['product_label']);
                                 //
                                $index_name ="CarteCadeau";
                                $chaine_details = $chaine_index[0].''.$chaine_index[1];
                                 
                                  if($chaine_details==$index_name){
                                  // detruire les articles du tableau.
                                    unset($data_lines[0]['lines'][$keys]);
                                     $array_data_gift_card[]=[
                                     "desc"=>'',
                                     "multicurrency_subprice"=> floatval($val['multicurrency_subprice']),
                                     "multicurrency_total_ht" => floatval($val['multicurrency_total_ht']),
                                     "multicurrency_total_tva" => floatval($val['multicurrency_total_tva']),
                                     "multicurrency_total_ttc" => floatval($val['multicurrency_total_ttc']),
                                     "product_ref" =>'', // reference du produit.(sku wwocommerce/ref produit dans facture invoice)
                                     "product_type"=>'',
                                     "product_label" =>$val['product_label'],
                                      "qty" => $val['qty'],
                                     "fk_product" =>$val['fk_product'],//  insert id product dans dolibar.
                                     "tva_tx" => '0',
                                      "ref_ext" => $val['ref_ext'], // simuler un champ pour socid pour identifié les produit du tiers dans la boucle /****** tres bon
                              ];
                             }
                             
                              $montant_carte_kdo[] = $val['multicurrency_total_ttc'];
                        }
                      }
                     }

                      // construire les données du clients a attacher a la facture.

                           // on ne cree pas l'attache de la seconde facture si la condition est respecté...
                           if(count($array_data_gift_card)==0){
                              $data_gift_card =[];
                              $data_options1 =[];
                              $chaine="";
                              $fid="";
                              $paimentids="";
                              $account_ids="";
                              $moyen_paiements="";
                              $product_data =[];
                              $fact_to=0;


                            }
                         
                           if(count($array_data_gift_card)!=0){
                                 $product_data = $array_data_gift_card;
                                 $fid =1;
                                 $paimentids="6";
                                 $account_ids="46";
                                 $moyen_paiements="6";
                                 $chaine="cado";
                                 $fact_to =1;
                             }
                          
                              // si le client a depensé une gift_card
                              // on ne cree pas l'attache de la seconde facture si la condition est respecté...
                              $data_options1 = [
                              "options_idw"=>$data_lines[0]['array_options']['options_idw'].'-'.$chaine,
                              "options_idc"=>'',
                              "options_fid"=>$fid,
                              "options_prepa" => $data_lines[0]['array_options']['options_prepa'],
                              "options_emba" => $data_lines[0]['array_options']['options_emba']
                          
                           ];
  
                     
                          $data_gift_card[]=[
                          'socid'=> $data_lines[0]['socid'],
                          'ref_client' =>'',
                          'date'=> $new_date,
                          "paye"=>"1",
                          "lines" =>$product_data,
                         'array_options'=> $data_options1,

                          ];

                    
                         if(count($array_data_gift_card)!=0){
                         // créer une 1 er facture dans le cas ou le client a achéte des bon d'achat ou les as dépenses. 
                               if($fact_to==1){
                                    foreach($data_gift_card as $donnes){
                                    // insérer les details des données de la facture dans dolibarr
                                     $retour_create = $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices", json_encode($donnes));
                                   }
          
                                  $inv = $retour_create;
                                // valider les commandes
                                   $newCommandepaye = [
                                        "paye"	=> 1,
                                        "statut"	=> 2,
                                        "mode_reglement_id"=>$moyen_paiements,
                                        "idwarehouse"=>6,
                                        "notrigger"=>0,
                                      ];

                                     // attribuer le compte bancaire.
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
                                    "paymentid"=>$paimentids,
                                    "closepaidinvoices"=> "yes",
                                     "accountid"=> $account_ids, // id du compte bancaire.
                                     "comment"=>"Achat de carte cadeaux via le site",
                                     "num_payment"=>$data_lines[0]['array_options']['options_idw'],
                                      ];

                                   // valider invoice
                                    $newCommandeValider = [
                                     "idwarehouse"	=> "6",
                                     "notrigger" => "0",
                                        ];
         
                                    // la valide et la mettre en payé......
                                  // Lier les factures dolibar  à un moyen de paiement et bank.
                                   $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$inv."/validate", json_encode($newCommandeValider));
                                   $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$inv."/payments", json_encode($newbank));
                                  // mettre le statut en payé dans la facture  dolibar
                                   $this->api->CallAPI("PUT", $apiKey, $apiUrl."invoices/".$inv, json_encode($newCommandepaye));
                               }
                                  // bloquer la création dans le cas ou le nombre de line est egal a 1.
                                  $nombre_reel = count($data_lines[0]['lines']);
                                  $nombre_virtuel = count($array_data_gift_card);
                              
                                  if($nombre_virtuel!=0 && $nombre_reel==0){
                                      $reel_indice=1;
                                }
                           
                            }

                          

                            if($reel_indice==1){
                               // bloquer la suite....
                               $message ="la commande est bien facturée et comporte uniquement que des  carte cadeaux";
                               echo json_encode(['success' => true, 'message'=> $message]);
                               exit;
                           }
                    
                           $retour_create_facture="";// gerer le retour de la création api.
                           foreach($data_lines as $donnes){
                            // insérer les details des données de la facture dans dolibarr
                             $retour_create = $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices", json_encode($donnes));
                           }
                            // traiter la réponse de l'api
                             $response = json_decode($retour_create, true);
                            if(isset($response['error']['message'])){
                               $message = $response['error']['message'];

                               $this->logError->insert(['order_id' => isset($orders[0]['order_woocommerce_id']) ? $orders[0]['order_woocommerce_id'] :  0, 'message' => $message]);

                               echo json_encode(['success' => false, 'message'=> $message]);
                               exit;
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
                           //if(count($info_tiers_flush)!=0){
                             // 
                             //$this->tiers->insert($info_tiers_flush['name'],$info_tiers_flush['name_alias'],$info_tiers_flush['socid'],$info_tiers_flush['code_client'],$info_tiers_flush['email'],$info_tiers_flush['phone'],$info_tiers_flush['address'],$info_tiers_flush['zip'],$info_tiers_flush['city'],$info_tiers_flush['date_created']);
                           // }
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

                  $status_dist = $this->getDistristatus();
                  // recupération les méthode de paiement.
                  $moyen_card = $this->commande->createpaiementid();
                  // recupérer les index_amout pour pour gérer les paiement des commandes.
                  $chaine_amount_true = $this->getAmountcard();
                   $index_amount_true = explode('%',$chaine_amount_true);
                   $chaine_index =",";
                     
                   // recupérer le status
                   if($index_amount_true[1]!=0 && $index_amount_true[1]!="liqpaid" && $index_amount_true[1]!="nobpp"){
                    // ici y'a eu paiement en LIQ et CB
                      $index_m ="CB";
                      $moyen_paid =  array_search($index_m,$moyen_card);
                      $moyen_paids = explode(',',$moyen_paid);
                      $mode_reglement_id = $moyen_paids[0];
                      $account_multiple="yes";
                     // j'accroche les compte bancaire.
                      
                   }

                   if($index_amount_true[1]=="liqpaid"){
                    // ici y'a eu paiement uniquement en LIQ
                
                      $index_m ="LIQ";
                      $moyen_paid =  array_search($index_m,$moyen_card);
                      $moyen_paids = explode(',',$moyen_paid);
                      $mode_reglement_id = $moyen_paids[0];
                      $account_multiple="yesliq";
                     // j'accroche les compte bancaire
            
                   }

                      // Qaund y'a un paiement uniquement que par CB 
                    if($index_amount_true[1]==0 && $index_amount_true[1]!="liqpaid"){
                        $index_m ="CB";
                        $moyen_paid =  array_search($index_m,$moyen_card);
                        $moyen_paids = explode(',',$moyen_paid);
                        $mode_reglement_id = $moyen_paids[0];
                        $account_multiple="no";
                       
                    }
                     
                       // si lacommande ne vient pas par un BPP(beauty proofs)
                    if($index_amount_true[1]=="nobpp"){
                         
                       $moyen_paid =  array_search($account_name,$moyen_card);
                           if($moyen_paid!=false){
                            $moyen_paids = explode(',',$moyen_paid);
                             $mode_reglement_id = $moyen_paids[0];
                          }else{
                           $account_name="payplug";
                            $mode_reglement_id =106;// fournir un paypplug par defaut. au cas il trouve pas.....
                          }

                          $account_multiple="no";
                     }
                    

                   $array_paiment = array('cod','vir_card1','vir_card','payplug','stripe','oney_x3_with_fees','oney_x4_with_fees','apple_pay','american_express','gift_card','bancontact','CB','PAYP');// carte bancaire....
                   $array_paiments = array('bacs', 'VIR');// virement bancaire id.....
                   $array_paimentss = array('DONS');
                   $array_espece =  array('LIQ');
                   $double_pai = array('CB,LIQ','LIQ,CB');// recupérer la methode de paiment....

                   if($account_multiple=="no"){
                       if(in_array($account_name,$array_paiment)) {
                       // defini le mode de paiment commme une carte bancaire...
                         //$mode_reglement_id = 6;
                         $account_id=4;// PROD 
                         $paimentid =6;// PROD
                       }

                       elseif(in_array($account_name,$array_paiments)){
                         // defini le paiment comme virement bancaire......
                       //$mode_reglement_id = 4;
                        $account_id=3; // PROD
                        $paimentid =3;// PROD
                     }

                      elseif(in_array($account_name,$array_paimentss)){
                         // CB
                           $account_id=4; // PROD
                           $paimentid=4;// PROD
                       }
                       else{
                          // CB
                          $account_id=4; // PROD
                          $paimentid =6;// PROD
                         }

                   }
                    
                   // paimement liquide.
                     if($account_multiple=="yesliq"){
                        $account_id=47;// PROD 
                        $paimentid =4;// PROD
                     }

                     if($account_multiple=="yes"){
                        $account_id=4;// PROD 
                        $paimentid =6;// PROD envoi en CB.
                   }
                   
                   //dump($account_multiple);
                   //dump($paimentid);
                   //dd($account_id);
                

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
                 "paymentid"=>$paimentid,
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
                      // traiter les retour de réponse api
                       $validate_facture =""; // retour de traitement de l'api.
                       $validate_facture =  $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$inv."/validate", json_encode($newCommandeValider));
                       // traiter la réponse de l'api
                       $response = json_decode($validate_facture, true);
                       $index_facture ="FA";// facture valide
                       $index_facture1 ="PR";// detecter une erreur  sur la validation souhaité d'une facture ....
                       if(!isset($response['ref'])){
                            $this->logError->insert(['order_id' => isset($orders[0]['order_woocommerce_id']) ? $orders[0]['order_woocommerce_id'] :  0, 'message' => 'erreur de validation de la facture restée impayée,veuillez la valider  !']);
                           echo json_encode(['success' => false, 'message'=> 'erreur de validation de la facture restée en brouillons,veuillez la valider  !']);
                          exit;
                        }  // recupérer le prefixe de la facture ces deux premiere lettre.
                    
                        if(isset($response['error']['message'])){
                          $message = $response['error']['message'];
                          $this->logError->insert(['order_id' => isset($orders[0]['order_woocommerce_id']) ? $orders[0]['order_woocommerce_id'] :  0, 'message' => $message]);
                          
                          echo json_encode(['success' => false, 'message'=> $message]);
                          exit;
                          
                      }

                        // mettre le statut en payé dans la facture  dolibar les commande preparation(uniquement internet)
                        if($account_multiple=="no"){
                              // Lier les factures dolibar  à un moyen de paiement et bank.
                            $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$inv."/payments", json_encode($newbank));
                             $this->api->CallAPI("PUT", $apiKey, $apiUrl."invoices/".$inv, json_encode($newCommandepaye));
                          // Lier les factures dolibar  à un moyen de paiement et bank.
                          }
                        

                         if($account_multiple=="yesliq"){  // liquide 100% BP

                             // Lier les factures dolibar  à un moyen de paiement et bank.
                               $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$inv."/payments", json_encode($newbank));
   
                              $this->api->CallAPI("PUT", $apiKey, $apiUrl."invoices/".$inv, json_encode($newCommandepaye));
                             // Lier les factures dolibar  à un moyen de paiement et bank...
   
                           }

                         if($account_multiple=="yes"){
                              
                                 // Les cas ou y'a des paiment en partie espece et CB pour la BP.
                                  // reconstruire le montant de la facture 
                                  $val_tax = $index_amount_true[1]*0.2;
                                    
              
                                   // Lier les factures dolibar  à un moyen de paiement et bank.
                                    $response_num = $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$inv."/payments", json_encode($newbank));
                                  
                                    $this->api->CallAPI("PUT", $apiKey, $apiUrl."invoices/".$inv, json_encode($newCommandepaye));
                                     // faire un select sur la table table paiment  
                                     $data = DB::connection('mysql2')->select("SELECT rowid,ref,num_paiement,fk_bank FROM llxyq_paiement WHERE rowid=$response_num");                                     
                                     $name_list = json_encode($data);
                                      $name_list = json_decode($name_list,true);
                                      // faire un update du amount.
                                       $ref_paiement = $name_list[0]['ref'];
                                       $index_row = explode('-',$ref_paiement);
                                       $index_pay = $index_row[1]+1;
                                        $fk_banks = $name_list[0]['fk_bank'];  
                                         $fk_bank = $name_list[0]['fk_bank']+1;// le fk bank suivant.
                                        $ref_definitive =  $index_row[0].'-'.$index_pay;
                                         $rowid_auto  = $name_list[0]['rowid']+1;
                                        // faire un update sur la ligne de la facture ...
                                        DB::connection('mysql2')
                                        ->table('llxyq_paiement_facture')
                                           ->where('fk_facture', '=', $inv)
                                           ->update(['amount' => $index_amount_true[0]]);
                                          // modifier le montant dans ligne de paiment
                                          DB::connection('mysql2')
                                           ->table('llxyq_paiement')
                                           ->where('rowid', '=', $response_num)
                                           ->update(['amount' => $index_amount_true[0], 'multicurrency_amount' => $index_amount_true[0]]);
                                        // Modifier dans l'ecriture de labanque avec le montant
                                         DB::connection('mysql2')
                                         ->table('llxyq_bank')
                                         ->where('rowid', '=', $fk_banks)
                                        ->update(['amount' => $index_amount_true[0]]);

                                       // faire un insert du montant en especé
                                       // faire un insert du montant en especé ici dans la banque
                                       DB::connection('mysql2')->table('llxyq_bank')->insert([
                                           'datec' => date('Y-m-d H:i:s'),
                                          'tms' => date('Y-m-d H:i:s'),
                                          'datev' =>date('Y-m-d H:i:s') ,
                                          'dateo' => date('Y-m-d H:i:s'),
                                          'amount' => $index_amount_true[1],
                                          'label' =>"Paiment en espèce Beauty proof paris 2024",
                                          'fk_account'=>47,
                                          'fk_user_author'=>0,
                                          'fk_user_rappro'=>0,
                                          'fk_type'=>'LIQ',
                                          'num_releve'=> '',
                                          'num_chq'=>$response['ref'],
                                          'numero_compte'=>'',
                                         'rappro'=>0,
                                          'note'=>'',
                                          'fk_bordereau'=>0,
                                         'banque'=>'',
                                          'emetteur'=>'',
                                           'author'=>'',
                                         'origin_id'=>0,
                                         'origin_type'=>'',
                                          'import_key'=>'',
                                        'amount_main_currency'=>0.00000000
                                      // Ajoutez d'autres colonnes et valeurs selon votre besoin
                                  ]);
  
                                      // faire un insert du paiement espece
                                        DB::connection('mysql2')->table('llxyq_paiement')->insert([
                                       'ref' => $ref_definitive,
                                        'ref_ext' => '',
                                        'entity' => 1,
                                        'datec' => date('Y-m-d H:i:s'),
                                         'tms' => date('Y-m-d H:i:s'),
                                        'datep' =>  date('Y-m-d H:i:s'),
                                        'amount' => $index_amount_true[1],
                                        'multicurrency_amount' =>$index_amount_true[1],
                                        'fk_paiement'=>4,
                                        'num_paiement'=>$name_list[0]['num_paiement'],
                                         'note'=> '',
                                         'ext_payment_id'=>'',
                                         'ext_payment_site'=>'',
                                          'fk_bank'=>$fk_bank,
                                          'fk_user_creat'=>0,
                                         'fk_user_modif'=>0,
                                          'fk_export_compta'=>0,
                                          'statut'=>0,
                                         'pos_change'=>0.00000000
                                      // Ajoutez d'autres colonnes et valeurs selon votre besoin
                                 ]);
  
                                 // faire un insert d'ecriture de paiement facture du montant en espéce.
                                   DB::connection('mysql2')->table('llxyq_paiement_facture')->insert([
                                       'fk_paiement' => $rowid_auto,
                                       'fk_facture' =>$inv,
                                       'amount' => $index_amount_true[1],
                                   // Ajoutez d'autres colonnes et valeurs selon votre besoin
                             ]);

                                   
                                
                                  
                           }
                        
                 }

        }


         public function Updatefacture($orders){
           // connexion api dolibar

           dd('service en attente ne pas utiliser pour l\'instant');

           $method = "GET";
            $apiKey = "VA05eq187SAKUm4h4I4x8sofCQ7jsHQd"; 
            $apiUrl = "https://www.poserp.elyamaje.com/api/index.php/";

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
           $product_construct_post =[];// construire le jeu de produit à envoyer.
           $data_construct =[];
           // le tableau pour valider les facture sur l'entrepot preics.
           // traiter les moyens de paimen
            // crée laccount paiement à partir de la methode de paiment.
            $array_paiment = array('cod','vir_card1','vir_card','payplug','stripe','oney_x3_with_fees','oney_x4_with_fees','apple_pay','american_express','gift_card','bancontact','CB','PAYP');// carte bancaire....
            $array_paiments = array('bacs', 'VIR');// virement bancaire id.....
            $array_paimentss = array('DONS');
            $valid="";
            $remise_percent="";
           foreach($orders as $values){
                // recupérer le fk_facture.
                $fk_facture = array_search($values['order_woocommerce_id'],$datas);
                // recupérer le moyen de payament
                $moyen_paid =  array_search($values['payment_method'],$moyen_card);
                $moyen_paids = explode(',',$moyen_paid);

                // recupérer le coupons si il existe
                $coupons = $values['coupons'];
                if($coupons==""){
                   $remise_percent="0";
                }else{
                     $chaine="fem";
                     if(strpos($coupons,$chaine)==true){
                        $remise_percent =0;
                     }else{
                          $remise_percent =10;
                     }
                }
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
                 
                 elseif($moyen_paids==false){
                     $moyen_paiement = "vir_card";
                     $mode_reglement_id=3;
                 }

                 // attribuer le compte de paiment ensuite.
                else if(in_array($moyen_paiement,$array_paiment)) {
                   // defini le mode de paiment commme une carte bancaire...
                  //$mode_reglement_id = 6;
                   $account_id=4;// PROD 
                   $paimentid =4;// PROD
               }

                elseif(in_array($moyen_paiement,$array_paiments)){
                  // defini le paiment comme virement bancaire......
                   //$mode_reglement_id = 4;
                    $account_id=6; // PROD
                    $paimentid =6;// PROD
                }

                elseif(in_array($moyen_paiement,$array_paimentss)){
                    // dons 
                     $account_id=4; // PROD
                      $paimentid =4;// PROD
                  }

                  else{
                    $account_id=4; // PROD
                    $paimentid =4;// PROD

                  }

                   $data_fk_facture[]= $fk_facture;// recupérer les id de facture depuis dolibar...
                   $data_product_construct =[];
                   foreach($values['line_items'] as $val){
                      $chaine = $val['quantity'].','.$val['subtotal'].','.$val['meta_data'][0]['value'];
                      $chaines = $values['order_woocommerce_id'].','.$val['quantity'].','.$val['subtotal'].','.$val['meta_data'][0]['value'];
                      $test_data[$chaine] = $val['meta_data'][0]['value'].','.$fk_facture;
                      
                       $data_construct[$chaines] = $values['order_woocommerce_id'].','.$val['meta_data'][0]['value'];
                        $product_construct_post[$values['order_woocommerce_id'].','.$fk_facture][] =[
                            'barcode'=>$values['order_woocommerce_id'].','.$val['meta_data'][0]['value']
                      ];

                  }

                  

                  // array pour paimement de la facture.
                    $newCommandepaye[$values['order_woocommerce_id'].','.$valid.','.$fk_facture] = [
                    "total_ht"  =>$values['total_order']-$values['total_tax_order'],
                    "total_tva" =>$values['total_tax_order'],
                    "total_ttc" =>$values['total_order'],
                     "paye"	=> 1,
                     "statut"	=> 2,
                     "mode_reglement_id"=>$mode_reglement_id,
                     "idwarehouse"=>17,
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

                  $account_id=4;
    
                  $newbank[$values['order_woocommerce_id'].','.$valid.','.$fk_facture] = [
                     "datepaye"=>$date_finale,
                     "paymentid"=>6,
                     "closepaidinvoices"=> "yes",
                     "accountid"=> $account_id, // id du compte bancaire.
                ];
          
                 // tableau pour valider les  factures
                  $newCommandeValider[$values['order_woocommerce_id'].','.$valid.','.$fk_facture] = [
                  "idwarehouse"	=> "17",
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
             $data_list_products =[];// recuperer les bon id de produit.
              foreach($listproduct as $values) {
                   if($values['barcode']!=""){
                   $data_list_product[$values['barcode']] = $values['id'];
                   $data_list_products[$values['id']] = $values['barcode'];
              }
              // tableau associatve entre ref et label product....
           }
          
           
           
          
        
           // recupérer et construire un tableau des products pour les réecrire dans la facture
           $data_update_product =[];
           $data_correction =[];
           foreach($product_construct_post as $keys=> $val){
                 foreach($val as $vad){
                   // recupérer le barcode pour aller le chercher le fk_product
                    $code = explode(',',$vad['barcode']);
                    $barcode = $code[1];
                    $fk_product = array_search($barcode,$data_list_products);
                    
                    $chaine_data = array_search($vad['barcode'],$data_construct);
                       if($chaine_data!=""){
                          $ch = explode(',',$chaine_data);
                            $data_update_product[$keys][] =[
                            "desc"=>"Desc",
                           'fk_product'=> $fk_product,
                           'qty'=> $ch[1],
                           'tva_tx'=> 20,
                           'subprice'=>$ch[2],
                           'remise_percent'=>$remise_percent,
                           'product_type'=> 1,
                           'rang'=> -1,
                           'fk_code_ventilation'=>0
                        
                      ];

                      $data_correction[] =[
                        'fk_product'=> $fk_product,
                        'qty'=> $ch[1],

                      ];
                 }
             }
          }

           
        

          // insert dans bdd
         foreach($data_update_product as $key=> $vacc){
              foreach($vacc as $vl){

                  $array_data[] =[
                   'fk_product'=>$vl['fk_product'],
                    'qty'=>$vl['qty']
                  
                  ];
            }

          }

        
          
      // compter le nombre de id_live 
        /*      $result = DB::table('data_lines_facts')
             ->select('fk_product' ,DB::raw('SUM(qty) as nombre_vente'))
             ->groupBy('fk_product')
             ->get();

              $name_list = json_encode($result);
              $name_list = json_decode($result,true);


             $this->csvcreateentrepot($name_list);
             

             dd('fin process');
             
             dd('fin');

               dd('succes');
          */

        
       //DB::table('data_lines_facts')->insert($array_data);


         // recupérer les ref (importatn effacer l'ecriture associe en base pour paiement important)
          $data_result =[];
           $ref_facture =[];
           $product_data =[];// construire un tableau pour un post (réécriture des lines products dans dolibarr)
            foreach($json_data as  $key => $valus){
               $ref_facture[] = $valus['ref'];
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

                  // recupérer
               }
            }
           }

           dd($result_finale);

          dd('fin de script');
            
             // Mettre les facture en brouillons et suprimer le compte lié
               $data_fact =[
                "idwarehouse"=>"6"
               ];

               // recupérer la ref du paiement pour les  factures pour les suprimer
               $ref_py =[];
                foreach($data_fk_facture as $vb){
                  $ref_pay[] = json_decode($this->api->CallAPI("GET", $apiKey, $apiUrl."invoices/".$vb."/payments"),true);
                }

                 $assoc_pay = $this->commande->getrowidfacture();// recupérer les id de paiment direcetement en base
              
                  foreach($ref_pay as $vf){
                     foreach($vf as $va){
                      $ref_py[] = array_search($va['ref'],$assoc_pay);
                   }
                }

                 // mettre la facture en brouillons.
                 foreach($data_fk_facture as $valu){
                   $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$valu."/settounpaid",json_encode($data_fact));
                   $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$valu."/settodraft");

               }

               // detruire dans la table lyq_facture_paiement les paiements associé à la facture.
                // icie

              
                
                foreach($data_fk_facture as $lk){
                   $deletepaiement  = DB::connection('mysql2')->select("DELETE FROM llxyq_paiement_facture WHERE fk_facture=$lk");
                   // suprimer ecriture paiement
                }

                // detruire la ligne du paiement
                foreach($ref_py as $id){
                   // suprimer les ligne d'ecriture de paiement avec la ref facture.
                   $deletepaiement  = DB::connection('mysql2')->select("DELETE FROM llxyq_paiement WHERE rowid=$id");
                }

                // detruire les ligne de produit dans la facture.
             //   foreach($data_fk_facture as $vj){
             //     $deletepaiement  = DB::connection('mysql2')->select("DELETE FROM llxyq_facturedet WHERE fk_facture=$vj");
             //   }

             
                // Mise à jours des ligne de product en masse(prix , quantité)
                  foreach($result_finale as $kyes => $valuss){
                      $ids_facture  = explode(',',$kyes);
                       // mettre à jours les factures 
                       $this->api->CallAPI("PUT", $apiKey, $apiUrl."invoices/".$ids_facture[0]."/lines/".$ids_facture[1]."",json_encode($valuss));
                       
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

         public function addpayments()
         {
             $method = "GET";
             $apiKey = "f2HAnva64Zf9MzY081Xw8y18rsVVMXaQ"; 
             $apiUrl = "https://www.transfertx.elyamaje.com/api/index.php/";

               // recupérer les bank account 
                // recupérer les prdoduct avec leur barcode pour utiliser plutard(important)
              $produitParam = ["limit" => 1600, "sortfield" => "rowid"];
              $listbank = $this->api->CallAPI("GET", $apiKey, $apiUrl."bankaccounts", $produitParam);
               // reference ref_client dans dolibar
               $listbanks = json_decode($listbank, true);// la liste des produits dans doliba. 

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
               // ecrire dans la banque card kdo. id 13
               $array_data =[
                 "date"=> $date_finale,
                 "type"=>"CADO",
                 "label"=>"Achat de carte cadeaux",
                 "amount"=>-50,
                 "cheque_number"=>"TC1-2310-37090",
                 "datev" => $date_finale,
                 ];

              // ecrire la ligne dans la bank 
                 $this->api->CallAPI("POST", $apiKey, $apiUrl."bankaccounts/13/lines/",json_encode($array_data));
                dd('opreration reussie');
               return $listbanks;
              
           

         }

         public function facture_gift(){

          if(count($array_data_gift_card)!=0){
            //
            $data_options = [
            "options_idw"=>'cdo-'.$donnees['order_id'].'',
           "options_idc"=>$coupons,
           "options_fid"=>1,
           "options_prepa" => $preparateur,
           "options_emba" => $emballeur,
           
            ];
        
             $data_gift_card[]=[
            'socid'=> $socid,
             'ref_client' =>$ref,
            'date'=> $new_date,
            "paye"=>"1",
            "lines" =>$array_data_gift_card,
            'array_options'=> $data_options,

         ];

            // crér le facture et la mettre en payée directement 
           foreach($data_gift_card as $donnes){
            // insérer les details des données de la facture dans dolibarr
             $retour_create = $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices", json_encode($donnes));
            }
            
            $inv = $retour_create;
             // valider les commandes
             $newCommandepaye = [
             "paye"	=> 1,
             "statut"	=> 2,
             "mode_reglement_id"=>6,
             "idwarehouse"=>6,
             "notrigger"=>0,
             ];

             // attribuer le compte bancaire.
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
              "paymentid"=>13,
              "closepaidinvoices"=> "yes",
              "accountid"=> 6, // id du compte bancaire.
             ];

               // valider invoice
              $newCommandeValider = [
               "idwarehouse"	=> "6",
               "notrigger" => "0",
              ];
            
              // la valide et la mettre en payé......
             // Lier les factures dolibar  à un moyen de paiement et bank.
             $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$inv."/validate", json_encode($newCommandeValider));
              $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$inv."/payments", json_encode($newbank));
             // mettre le statut en payé dans la facture  dolibar
             $this->api->CallAPI("PUT", $apiKey, $apiUrl."invoices/".$inv, json_encode($newCommandepaye));

        }


         }


         public function csvcreateentrepot(array $data)
         {
             
                 $filename = "stocks_correction.csv";
                 $fp = fopen('php://output', 'w');
                   // créer une entete du tableau .
                   $header = array('fk_product','quantite');
                   // gérer les entete du csv 
                  header('Content-type: application/csv');
                 header('Content-Disposition: attachment; filename=' . $filename);
                 fputcsv($fp, $header);
                 
                 
                 foreach ($data as $row) {
                 fputcsv($fp, $row);
               }
               exit();
             
       }

       
   

  }


