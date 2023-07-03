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

class TransferOrder
{
    
      private $api;
      
      private $commande;
      private $dataidcommande;// recupérer les ids commande existant
      private $status; // vartiable string pour statuts(customer et distributeur)
      private $countd = []; // les clients distributeur
      private $countc = [];// les clients non distributeur
    
       public function __construct(Api $api,
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
   
  

     /** 
     *@return array
     */
      public function Transferorder($orders)
      {
            
             // excercer un get et post et put en fonction des status ...
               // recuperer les données api dolibar copie projet tranfer x.
             
              
               $method = "GET";
               $apiKey = env('KEY_API_DOLIBAR');
               $apiUrl = env('KEY_API_URL');
    
                 $produitParam = ["limit" => 800, "sortfield" => "rowid"];
	               $listproduct = $this->api->CallAPI("GET", $apiKey, $apiUrl."products", $produitParam);
                 // reference ref_client dans dolibar
                 $listproduct = json_decode($listproduct, true);// la liste des produits dans doliba
                //Recuperer les ref_client existant dans dolibar
	               $tiers_ref = "";
                 // recupérer directement les tiers de puis bdd.
                 //$this->tiers->insertiers();// mise a jour api
                 $list_tier = $this->tiers->getalltiers();// recupérer les tiers a jours .
                 // recuperer les ids commandes
                 $ids_commande = $this->commande->getAll(); // tableau pour recupérer les id_commande 
                 $key_commande = $this->commande->getIds();// lindex les ids commande existant.
                 // recupérer le tableau de ids
                 $ids_commandes =[];
              
                  foreach($ids_commande as $key => $valis) {
                     $ids_commandes[$valis['id_commande']] = $key;
                  }
            
                  // recupérer les email,socid, code client existant dans tiers
                  $data_email = [];//entre le code_client et email.
                  $data_list = []; //tableau associative de id et email
                  $data_code =[];// tableau associative entre id(socid et le code client )
                  foreach($list_tier as $val) {
                     $data_email[$val['code_client']] = $val['email'];
                     if($val['email']!="") {
                       $data_list[$val['socid']] = $val['email'];
                     }
                      // recuperer id customer du client et créer un tableau associative.
                      $code_cl = explode('-',$val['code_client']);
                      if(count($code_cl)>2){
                        $code_cls = $code_cl[2];
                        $data_code[$val['socid']] = $code_cls;
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
      
                  foreach($listproduct as $values){
                     $data_list_product[$values['id']] = $values['barcode'];
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
                
                    foreach($orders as $k => $donnees) {
                            // créer des tiers pour dolibarr via les datas woocomerce. 
                            // créer le client via dolibarr à partir de woocomerce.
                            $ref_client = rand(4,10);
                            // recupérer id du tiers en fonction de son email...
                            $fk_tiers = array_search($donnees['billing']['email'],$data_list);
                            // recupérer id en fonction du customer id
                            $fk_tier = array_search($donnees['customer_id'],$data_code);
                      
                           if($fk_tiers!="") {
                             $socid = $fk_tiers;
                            
                             }

                           // construire le tableau
                             if($fk_tier!="" && $fk_tiers==""){
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

        
                            if($fk_tiers=="" && $fk_tier=="") {
                                   
                                    $date = date('Y-m-d');
                                    $dat = explode('-', $date);
                                    $a1 = $dat[0];
                                    // recupérer les deux deniers chiffre;
                                    $a11= substr($a1,-2);
                                    $a2 = $dat[1];
                                 
                                   $socid = $id_cl;
                                   $woo ="woocommerce";
                                    $name="";
                                   $code = $donnees['customer_id'];//customer_id dans woocomerce
                                   $code_client ="WC-$a2$a11-$code";// créer le code client du tiers.
                                  
                                    $data_tiers[] =[ 
                                   'entity' =>'1',
                                   'name'=> $donnees['billing']['first_name'].' '.$donnees['billing']['last_name'],
                                   'name_alias' => $woo,
                                   'address' => $donnees['billing']['address_1'],
                                   'zip' => $donnees['billing']['postcode'],
                                   'email' => $donnees['billing']['email'],
                                   'phone' => $donnees['billing']['phone'],
                                    'client' 	=> '1',
                                    'code_client'	=> $code_client,
                                    'country_code'=> $donnees['billing']['country']
                                 ];
                                 

                                   $data_infos_user = [
                                        'first_name'=> $donnees['billing']['first_name'].' '.$donnees['billing']['last_name'],
                                        'last_name' =>'',
                                        'email'=>$donnees['billing']['email'],
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
                                             // details  array article libéllé(product sur la commande) pour dolibarr.
                                             // recupérer les données du kdo
                                            if($values['subtotal']==0){
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
                                        // recupérer les les produits dont les barcode ne sont pas reconnu.
                                        $ref_sku="";
                                        $list = new Transfertrefunded();
                                        $list->id_commande = $donnees['order_id'];
                                        $list->ref_sku = $ref_sku;
                                        $list->name_product = $values['name'];
                                        $list->quantite = $values['quantity'];
                                        $list->save();
                                     }
                                 }
                           }       
                                  
                                // verifier si la commande est nouvelle
                                //lié le client les  produits qui compose son achat .
                                if(isset($key_commande[$donnees['order_id']])==false) {
                                     // formalisés les valeurs de champs ajoutés id_commande et coupons de la commande.

                                     
                                       $data_options = [
                                       "options_idw"=>$donnees['order_id'],
                                       "options_idc"=>$donnees['coupons']
                                       ];
                                      
                                       
                                        // pour les factures non distributeurs...
                                        $d=1;
                                        $ref="";
                                        $cb ="CB";
                                        $data_lines[] = [
                                       'socid'=> $socid,
                                       'ref_client' =>$ref,
                                       "email" => $donnees['billing']['email'],
                                       "remise_percent"=> floatval($donnees['discount_amount']),
                                        "total_ht"  =>floatval($donnees['total_order']-$donnees['total_tax_order']),
                                        "total_tva" =>floatval($donnees['total_tax_order']),
                                       "total_ttc" =>floatval($donnees['total_order']),
                                        "paye"=>"1",
                                        "mode_reglement_code"=> $cb,
                                        'lines' =>$data_product,
                                        'array_options'=> $data_options,
                                    
                                      ];

                                      $data_options_kdo = [
                                        "order_id"=>$donnees['order_id'],
                                        "coupons"=>$donnees['coupons'],
                                        "total_order"=> floatval($donnees['total_order']),
                                        "date_order" => $donnees['date'],
                                       ];
                                        
                                      // insert dans base de donnees historiquesidcommandes
                                       $date = date('Y-m-d');
                                       $historique = new Commandeid();
                                       $historique->id_commande = $donnees['order_id'];
                                       $historique->date = $date;
                                        // insert to
                                       $historique->save();
                                   }
                                    else{

                                      
                                         $data_tiers = [];
                                         $data_kdo = [];// si le details est deja crée via un order_id.
                                         $data_infos_user =[];
                                         $data_options_kdo =[];

                                    }
                                    // recupérer les id_commande deja pris
                                    if(isset($key_commande[$donnees['order_id']])==true) {
                                        // .....
                                        $id_commande_existe[] = $donnees['order_id'];
                                    }
                    
                      }

                      
                      
                         // recupérer les deux variable dans les setter.
                          //$this->setCountd($orders_distributeur);// recupérer le tableau distributeur la variale.
                          // $this->setCountc($orders_d);// recupérer le tableau des id commande non distributeur
                          // filtrer les doublons du tableau
                           $id_commande_exist = array_unique($id_commande_existe);
                         // recupérer le tableau
                          $this->setDataidcommande($id_commande_exist);
                          // renvoyer un tableau unique par tiers via le socid.
                          // données des non distributeurs
                          $temp = array_unique(array_column($data_lines, 'socid'));
                          $unique_arr = array_intersect_key($data_lines, $temp);

                        // trier les produits qui ne sont pas en kdo
                       foreach($unique_arr as $r => $val){
                           foreach($val['lines'] as $q => $vak) {
                             if($val['socid']!=$vak['ref_ext']){
                                unset($unique_arr[$r]['lines'][$q]); // filtrer les produit qui n'appartienne pas à l'utilisateur les enléves.
                             }
                           }
                      }

                       // Traiter  Les données des cadeaux .
                       // merger le client et les data coupons.
                         $data_infos_order  = array_merge($data_infos_user,$data_options_kdo);

                         dump($data_infos_order);
                         $tiers_exist = $this->don->gettiers();

                         dd($tiers_exist);
                         // insert le tiers dans la BDD.
                         if(count($data_infos_order)!=0){
                            // insert 
                          
                           dd($tiers_exist);
                           if(isset($tiers_exist[$data_infos_order['email']])==false){
                            $this->don->inserts($data_infos_order['first_name'],$data_infos_order['last_name'],$data_infos_order['email'],$data_infos_order['order_id'],$data_infos_order['coupons'],$data_infos_order['total_order'],$data_infos_order['date_order']);
                            // JOINTRE les produits.
                           }
                       }
                        
                        // recupérer les cadeaux associé a l'utilisateur.
                        dd($data_kdo);
                         if(count($data_kdo)!=0){
                              $this->dons->inserts($data_kdo);
                          }
                       
                        
                         
                        foreach($data_tiers as $data) {
                        // insérer les données tiers dans dolibar
                         $this->api->CallAPI("POST", $apiKey, $apiUrl."thirdparties", json_encode($data));
                      }
                    
                      foreach($unique_arr as $donnes){
                         // insérer les details des données de la facture dans dolibarr
                         $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices", json_encode($donnes));
                      }
                        
                        // activer le statut payé et lié les paiments  sur les factures.
                         $this->invoicespay($orders);

                         

                         dd('succes of opération');
                        // initialiser un array recuperer les ref client.
                        return view('apidolibar');
                
        }
        
        public function invoicespay($orders)
        {
           
            $method = "GET";
            $apiKey = "0lu0P9l4gx9H9hV4G7aUIYgaJQ2UCf3a";
            $apiUrl = "https://www.transfertx.elyamaje.com/api/index.php/";
           
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

              // recupération du dernier id invoices dolibar
              $inv="";
              foreach($invoices_id as $vk) {
                $inv = $vk['id'];
              }
              // recupérer le premier id de la facture
             foreach($invoices_asc as $vks){
               $inc = $vks['id'];
             }
            
             foreach($clientSearch as $data) {
               $tiers_ref = $data['id'];
             }
        
               // le nombre recupérer 
               $count_datas = $orders;// retour array ici
               $ids_orders =[];// recupérer les id commande venant de woocomerce
               $data_ids=[];// recupérer les nouveaux ids de commande jamais utilisés
        
              foreach($count_datas as $k =>$valis){
                     $ids_orders[] = $valis['id'];
                     if(!in_array($valis['id'],$this->getDataidcommande()))
                      {
                        $data_ids[]= $valis['id'];
                      }
               }
           
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
                 if($nombre_orders == 0) {
                    $label = "Aucune commande transférée";
                 }
                 elseif($nombre_orders ==1){
                    $label ="la commande à été transférée dans dolibars le $datetime";
                 }
               else{
               $label = "$nombre_orders commandes transférées dans dolibars le $datetime";
              }
         
                // insert dans la table 
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
             
                $newCommandepaye = [
                 "paye"	=> 1,
                 "statut"	=> 2,
                 "mode_reglement_id"=>4,
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
           "accountid"=> 6, // id du compte bancaire.
        ];
           
             $fac = intval($inv+1);
             $facs = intval($inv);
             
              // valider les facture dans dolibar
              $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$inv."/validate", json_encode($newCommandeValider));
              // mettre le statut en payé dans la facture  dolibar
              $this->api->CallAPI("PUT", $apiKey, $apiUrl."invoices/".$inv, json_encode($newCommandepaye));

               // Lier les factures dolibar  à un moyen de paiement et bank.
               $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$inv."/payments", json_encode($newbank));
    }

      /** 
     *@return array
     */
   

  }
     
    




