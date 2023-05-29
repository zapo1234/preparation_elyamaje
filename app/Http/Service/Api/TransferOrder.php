<?php
namespace App\Http\Service\Api;

use Illuminate\Support\Facades\Http;
use App\Models\Commandeid;
use App\Models\Productdiff;
use App\Models\Transfertrefunded;
use App\Models\Transfertsucce;
use App\Models\Distributeur\Invoicesdistributeur;
use App\Repository\Commandeids\CommandeidsRepository;
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
       TiersRepository $tiers)
       {
         $this->api=$api;
         $this->commande = $commande;
         $this->tiers = $tiers;
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
   
  
     
      
      public function getdataorderid($id)
      {
             $urls="https://www.elyamaje.com/wp-json/wc/v3/orders/$id?&consumer_key=ck_06dc2c28faab06e6532ecee8a548d3d198410969&consumer_secret=cs_a11995d7bd9cf2e95c70653f190f9feedb52e694";
              // recupérer des donnees orders de woocomerce depuis api
              $donnes = $this->api->getDataApiWoocommerce($urls);
             $donnees[] = array_merge($donnes);
             
            return $donnees;
      }
      
      // 
      public function getDataorder($date_after,$date_before)
      {
             $donnees = [];
           // boucle sur le nombre de paginations trouvées
          for($i=1; $i<3; $i++)
          {
              $urls="https://www.elyamaje.com/wp-json/wc/v3/orders?orderby=date&order=desc&after=$date_after&before=$date_before&consumer_key=ck_06dc2c28faab06e6532ecee8a548d3d198410969&consumer_secret=cs_a11995d7bd9cf2e95c70653f190f9feedb52e694&page=$i&per_page=100";
              // recupérer des donnees orders de woocomerce depuis api
              $donnes = $this->api->getDataApiWoocommerce($urls);
             $donnees[] = array_merge($donnes);
           }
           
           return $donnees;
      }
      
      
      public function getdataproduct()
      {
          
        // boucle sur le nombre de paginations trouvées
          for($i=1; $i<9; $i++)
          {
              
             $urls="https://www.elyamaje.com/wp-json/wc/v3/products?consumer_key=ck_06dc2c28faab06e6532ecee8a548d3d198410969&consumer_secret=cs_a11995d7bd9cf2e95c70653f190f9feedb52e694&page=$i&per_page=100";
              // recupérer des donnees orders de woocomerce depuis api
              $donnes = $this->api->getDataApiWoocommerce($urls);
             $donnees[] = array_merge($donnes);
           }
           // recuperer les produit (name et les sku  de ces produits)
           $product_list = [];
           foreach($donnees as $k => $values)
           {
               foreach($values as $val)
               {
                 $product_list[$val['sku']]=$val['name'];
               
               }
           }
              return $product_list;
              
      }
      
      
       public function getDataorders()
       {
        
	         // recuperer les données api dolibar copie projet tranfer x.
              $method = "GET";
              $apiKey = "0lu0P9l4gx9H9hV4G7aUIYgaJQ2UCf3a";
               $apiUrl = "https://www.transfertx.elyamaje.com/api/index.php/";
           
              //environement test local
           
               //Recuperer les ref et id product dans un tableau
	   
	           $produitParam = ["limit" => 700, "sortfield" => "rowid"];
	            $listproduct = $this->api->CallAPI("GET", $apiKey, $apiUrl."products", $produitParam);
	            
	             $lists = json_decode($listproduct,true);
	            
	            foreach($lists as $values)
               {
                  // tableau associatve entre ref et label product
                  $product_datas[$values['ref']] = $values['label'];
         
              }
      
           
            return $product_datas;
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
     *@return array
     */
      public function Transferorder($orders)
      {
               // excercer un get et post et put en fonction des status ...
               // recuperer les données api dolibar copie projet tranfer x.
                $method = "GET";
                $apiKey = "0lu0P9l4gx9H9hV4G7aUIYgaJQ2UCf3a";
                $apiUrl = "https://www.transfertx.elyamaje.com/api/index.php/";
	   
	               $produitParam = ["limit" => 700, "sortfield" => "rowid"];
	               $listproduct = $this->api->CallAPI("GET", $apiKey, $apiUrl."products", $produitParam);
                 // reference ref_client dans dolibar
                 $listproduct = json_decode($listproduct, true);// la liste des produits dans dolibar
                 //Recuperer les ref_client existant dans dolibar
	               $tiers_ref = "";
                 // recupérer directement les tiers de puis bdd.
                 $this->tiers->insertiers();// mise a jour api
                 $list_tier = $this->tiers->getalltiers();// recupérer les tiers a jours .
                 // recuperer les ids commandes
                 $ids_commande = $this->commande->getAll(); // tableau pour recupérer les id_commande 
                 $key_commande = $this->commande->getIds();// lindex les ids commande existant.
                 // recupérer le tableau de ids
                 $ids_commandes =[];
              
                  foreach($ids_commande as $key => $valis)
                  {
                     $ids_commandes[$valis['id_commande']] = $key;
                  }
            
                  // recupérer les email,socid, code client existant dans tiers
                  $data_email = [];//entre le code_client et email.
                  $data_list = []; //tableau associative de id et email
                  $data_code =[];// tableau associative entre id(socid et le code client )
                  foreach($list_tier as $val)
                  {
                     $data_email[$val['code_client']] = $val['email'];
                     if($val['email']!="")
                     {
                       $data_list[$val['socid']] = $val['email'];
                     }
                      // recuperer id customer du client et créer un tableau associative.
                      $code_cl = explode('-',$val['code_client']);
                      if(count($code_cl)>2)
                      {
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

                 foreach($clientSearch as $data)
                 {
                    $tiers_ref = $data['id'];
                 }
                  // convertir en entier la valeur le dernier id du tiers=>socid.
                  $id_cl = (int)$tiers_ref;
                  $id_cl = $id_cl+1;
                  $socid ="";
                   $data_list_product =[];// tableau associative entre le ean barcode et id_produit via dollibar
      
                  foreach($listproduct as $values)
                  {
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
                
                    foreach($orders as $k => $donnees)
                    {
                            // créer des tiers pour dolibarr via les datas woocomerce. 
                            // créer le client via dolibarr à partir de woocomerce.
                            $ref_client = rand(4,10);
                            // recupérer id du tiers en fonction de son email...
                            $fk_tiers = array_search($donnees['billing']['email'],$data_list);
                            // recupérer id en fonction du customer id
                            $fk_tier = array_search($donnees['customer_id'],$data_code);
                      
                           if($fk_tiers!="")
                           {
                             $socid = $fk_tiers;
                           }
        
                           if($fk_tier!="" && $fk_tiers=="")
                           {
                               $socid = $fk_tier;
                           }
        
                            if($fk_tiers=="" && $fk_tier=="")
                            {
                                   
                                   $date = date('Y-m-d');
                                   $dat = explode('-', $date);
                                   $a1 = $dat[0];
                                   // recupérer les deux deniers chiffre;
                                   $a11= substr($a1,-2);
                                   $a2 = $dat[1];
                                 
                                   $socid = $id_cl++;
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
           
                              }

                             foreach($donnees['line_items'] as $key => $values)
                             {
                                  foreach($values['meta_data'] as $val)
                                  {
                                     //verifié et recupérer id keys existant de l'article// a mettre à jour en vrai. pour les barcode
                                       if($val['value']!=null)
                                       {
                                          $fk_product = array_search($val['value'],$data_list_product); // fournir le barcode  de woocommerce  =  barcode  dolibar pour capter id product
                                       }
                                       else{
                                         $fk_product="";
                                      }
                                      $ref="";
                                     
                                      if($fk_product!="")
                                      {
                                         // details  array article libéllé(product sur la commande) pour dolibarr.
                                          $data_product[] = [
                                            "multicurrency_subprice"=> floatval($values['subtotal']),
                                            "multicurrency_total_ht" => floatval($values['subtotal']),
                                            "multicurrency_total_tva" => floatval($values['total_tax']),
                                            "multicurrency_total_ttc" => floatval($values['total']),
                                            "product_ref" => $ref, // reference du produit.(sku wwocommerce/ref produit dans facture invoice)
                                            "product_label" =>$values['name'],
                                            "qty" => $values['quantity'],
                                            "fk_product" => $fk_product,//  insert id product dans dolibar.
                                            "ref_ext" => $socid, // simuler un champ pour socid pour identifié les produit du tiers dans la boucle /****** tres bon
                                        ];

                                     }
               
                                     if($fk_product=="")
                                     {
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
                                if($this->testing($key_commande,$donnees['order_id'])==false)
                                {
                                     // formalisés les valeurs de champs ajoutés id_commande et coupons de la commande.
                                     $array_options[] = [
                                      "options_idw"=>$donnees['order_id'],
                                      "options_idc"=>$donnees['coupons']
                                       ];
                                
                                       // pour les factures non distributeurs...
                                        $d=1;
                                        $ref="";
                                        $data_lines[] = [
                                       'socid'=> $socid,
                                       'ref_int' =>$d,
                                       'ref_client' =>$ref,
                                       "email" => $donnees['billing']['email'],
                                       "remise_percent"=> floatval($donnees['discount_amount']),
                                        "total_ht"  =>floatval($donnees['total_order']),
                                        "total_tva" =>floatval($donnees['total_tax_order']),
                                       "total_ttc" =>floatval($donnees['total_order']),
                                        "paye"=>"1",
                                        'lines' =>$data_product,
                                        'array_options'=> $array_options,
                                    
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
                                    }
                                    // recupérer les id_commande deja pris
                                   if($this->testing($key_commande,$donnees['order_id'])==true)
                                   {
                                     $id_commande_existe[] = $donnees['order_id'];
                                   }
                    
                      }
     
                       // recupérer les deux variable dans les seter
                       $this->setCountd($orders_distributeur);// recupérer le tableau distributeur la variale.
                       $this->setCountc($orders_d);// recupérer le tableau des id commande non distributeur
                       // filtrer les doublons du tableau
                       $id_commande_exist = array_unique($id_commande_existe);
                       // recupérer le tableau
                       $this->setDataidcommande($id_commande_exist);
                       // renvoyer un tableau unique par tiers via le socid.
                       // données des non distributeurs
                       $temp = array_unique(array_column($data_lines, 'socid'));
                       $unique_arr = array_intersect_key($data_lines, $temp);
            
                       foreach($unique_arr as $r => $val)
                       {
                           foreach($val['lines'] as $q => $vak)
                           {
                             if($val['socid']!=$vak['ref_ext'])
                             {
                               unset($unique_arr[$r]['lines'][$q]); // filtrer les produit qui n'appartienne pas à l'utilisateur les enléves.
                             }
                           }
                      }
                
                      foreach($data_tiers as $data)
                      {
                        // insérer les données tiers dans dolibar
                         $this->api->CallAPI("POST", $apiKey, $apiUrl."thirdparties", json_encode($data));
                      }
                    
                      foreach($unique_arr as $donnes)
                      {
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
              foreach($invoices_id as $vk)
              {
                $inv = $vk['id'];
              }

              // recupérer le premier id de la facture
             foreach($invoices_asc as $vks)
             {
               $inc = $vks['id'];
             }
            
             foreach($clientSearch as $data)
             {
               $tiers_ref = $data['id'];
             }
        
               // le nombre recupérer 
               $count_datas = $orders;// retour array ici
               $ids_orders =[];// recupérer les id commande venant de woocomerce
               $data_ids=[];// recupérer les nouveaux ids de commande jamais utilisés
        
              foreach($count_datas as $k =>$valis)
              {
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
                 if($nombre_orders == 0)
                 {
                    $label = "Aucune commande transférée";
                 }
                 elseif($nombre_orders ==1)
                 {
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

           // valider les facture dans dolibar
           for($i=$nombre_count; $i<$inv+2; $i++)
           {
              $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$i."/validate", json_encode($newCommandeValider));
           }
      
             // Lier les factures dolibar  à un moyen de paiement et bank.
           for($i=$nombre_count; $i<$inv+2; $i++)
           {
               $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$i."/payments", json_encode($newbank));
           }

              // mettre le statut en payé dans la facture  dolibar
           for($i=$nombre_count; $i<$inv+2; $i++)
           {
             $this->api->CallAPI("PUT", $apiKey, $apiUrl."invoices/".$i, json_encode($newCommandepaye));
           }

     }







       /** 
     *@return array
     */
    public function Transferorders()
    {
           $id=73777;
           $order = $this->getdataorderid($id);// pour une seul commande. retour de réponse tableau. $order
           dd($order);
            // excedd(rcer un get et post et put en fonction des status .
            // recuperer les données api dolibar copie projet tranfer x.
             $method = "GET";
             $apiKey = "0lu0P9l4gx9H9hV4G7aUIYgaJQ2UCf3a";
             $apiUrl = "https://www.transfertx.elyamaje.com/api/index.php/";
         
              //environement test local
         
              //Recuperer les ref et id product dans un tableau
   
              $produitParam = ["limit" => 700, "sortfield" => "rowid"];
              $listproduct = $this->api->CallAPI("GET", $apiKey, $apiUrl."products", $produitParam);
               // reference ref_client dans dolibar
               $listproduct = json_decode($listproduct, true);// la liste des produits dans dolibar
              
      
              //Recuperer les ref_client existant dans dolibar
              $tiers_ref = "";
             
              // recupérer directement les tiers de puis bdd.
              $this->tiers->insertiers();// mise a jour api
              $list_tier = $this->tiers->getalltiers();// recupérer les tiers a jours .
            
               // recuperer les ids commandes deja traiter
               $ids_commande = $this->commande->getAll(); // tableau pour recupérer les id_commande 
               $key_commande = $this->commande->getIds();// lindex les ids commande existant.

               // recupérer le tableau de ids.
               $ids_commandes =[];
               foreach($ids_commande as $key => $valis)
               {
                 $ids_commandes[$valis['id_commande']] = $key;
               }
                 // recupérer les email existant dans tiers
                $data_email = [];//entre le code_client et email.
                $data_list = []; //tableau associative de id et email
                $data_code =[];// tableau associative entre id(socid et le code client )
   
                foreach($list_tier as $val)
                {
                  $data_email[$val['code_client']] = $val['email'];
             
                  if($val['email']!="")
                  {
                     $data_list[$val['socid']] = $val['email'];
                  }
                
                   // recuperer id customer du client et créer un tableau associative.
                  $code_cl = explode('-',$val['code_client']);
                  if(count($code_cl)>2)
                  {
                     $code_cls = $code_cl[2];
                     $data_code[$val['socid']] = $code_cls;
                  }
      
              }

                 // recuperer dans un tableau les ref_client existant(le dernier  id du tiers dans dolibar.
                 $clientSearch = json_decode($this->api->CallAPI("GET", $apiKey, $apiUrl."thirdparties", array(
                 "sortfield" => "t.rowid", 
                 "sortorder" => "DESC", 
                 "limit" => "1", 
                 "mode" => "1",
                 )
                ), true);

                foreach($clientSearch as $data)
                {
                  $tiers_ref = $data['id'];
                }

              
   
                  // convertir en entier la valeur le dernier id du tiers=>socid.
                  $id_cl = (int)$tiers_ref;
                  $id_cl = $id_cl+1;
                  $socid ="";
                  // recupérer  les données dans un tableau associative(id et ref_article) dans dolibar
                  $data_list_product =[];// tableau associative entre le ean barcode et id_produit via dollibar
    
                 foreach($listproduct as $values)
                 {
                    $product_data[$values['id']]= $values['ref'];// tableau associative entre id product et reférence(product)
                    $data_list_product[$values['id']] = $values['barcode'];
                    // tableau associatve entre ref et label product....
                 }

                  // recupére les orders des données provenant de  woocomerce
                   // appel du service via api
                   $order_data = $this->getdataorderid($id);// pour une seul commande. retour de réponse tableau. $order
                   //$oders_datas =  $this->getDataorder($date_after,$date_before);// retour des orders woocomerce!
                   $data_tiers = [];//data tiers dans dolibar
                   $data_lines  = [];// data article liée à commande du tiers en cours
                   $data_product =[]; // data article details sur commande facture
                   $data = [];
                   $lines =[]; // le details des articles produit achétés par le client
             
                  $id_commande_existe =[];// recupérer les id_commande existant deja récupérer dans les facture
             
                  $orders_d = [];// le nombre de orders non distributeur
                  $orders_distributeur = [];// le nombre de orders des distributeurs...
             
                  foreach($order as $k => $donnees)
                  {
               
                         // recupérer les données pour les tiers pour dolibar post tiers dans l'array
                         // créer le client via dolibarr à partir de woocomerce.
                         $ref_client = rand(4,10);
                          //verifié et recupérer id keys existant de l'article
                          $fk_tiers = array_search($donnees['billing']['email'],$data_list);
                          // recupérer id en fonction du customer id
                          $fk_tier = array_search($donnees['customer_id'],$data_code);
                          // recupérer le code customer client
                          //$fk_tier = array_search($donnees['customer_id'],$data_code).
                         if($fk_tiers!="")
                         {
                           $socid = $fk_tiers;
                         }
      
                         if($fk_tier!="" && $fk_tiers=="")
                         {
                             $socid = $fk_tier;
                         }
      
                          if($fk_tiers=="" && $fk_tier=="")
                           {
                                 
                                 $date = date('Y-m-d');
                                 $dat = explode('-', $date);
                                 $a1 = $dat[0];
                                 // recupérer les deux deniers chiffre;
                                 $a11= substr($a1,-2);
                                 $a2 = $dat[1];
                               
                                 $socid = $id_cl++;
                                 $woo ="woocommerce";
                                  $name="";
                                 $code = $donnees['customer_id'];//customer_id dans woocomerce
                                 $code_client ="WC-$a2$a11-$code";// recupérer le customer id dans wocoomerce
                                
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
         
 
                          }

                      
                           foreach($donnees['line_items'] as $key => $values)
                           {
                              
                              foreach($values['meta_data'] as $vals)
                              {
                                 //verifié et recupérer id keys existant de l'article// a mettre à jour en vrai. pour les barcode
                                if($vals['key']=="_reduced_stock")
                                {
                                  // construire le details des produits arrivant liée pour dolibarr.
                                  $fk_product = array_search($vals['value'],$data_list_product); // fournir le barcode  de woocommerce  =  barcode  product de dolibar pour capter id du produit
                               
                                   if($fk_product!="")
                                   {
                                       // details  array article libéllé(product sur la commande) pour dolibar
                                       // details des produits, quantité et prix  dans une facture.
                                       $data_product[] = [
                                        "multicurrency_subprice"=> floatval($values['subtotal']),
                                        "multicurrency_total_ht" => floatval($values['subtotal']),
                                        "multicurrency_total_tva" => floatval($values['total_tax']),
                                        "multicurrency_total_ttc" => floatval($values['total']),
                                        "product_ref" => $values['sku'], // reference du produit.(sku wwocommerce/ref produit dans facture invoice)
                                        "product_label" =>$values['name'],
                                         "qty" => $values['quantity'],
                                         "fk_product" => $fk_product,// id product dans dolibar.
                                          "ref_ext" => $socid, // simuler un champ pour socid pour identifié les produit du tiers dans la boucle /****** tres bon
                                      ];

                                   }
             
                                }
                              }
                    
                               if($fk_product=="")
                               {
                                 $list = new Transfertrefunded();
                                 $list->id_commande = $donnees['id'];
                                 $list->ref_sku = $values['sku'];
                                 $list->name_product = $values['name'];
                                 $list->quantite = $values['quantity'];
                                 $list->save();
                               }
                  
               
                          }       
                      
                             // verifier si la commande est nouvelle
                             //lié le client avec les produits de ses achats 
                             if($this->testing($key_commande,$donnees['id'])==false)
                             {
                                  // pour les facture non distributeur...
                                   // formalisés les valeurs de champs ajoutés id_commande et coupons de la commande.
                                   $d=1;
                                  
                                  $data_lines[] = [
                                  "socid"=> $socid,
                                  "ref_client" =>$donnees['id'],// fournir un id orders wocommerce dans dolibar...
                                  "remise_percent"=>null,
                                  "email" => $donnees['billing']['email'],
                                  "total_ht"  =>"0.00000000",
                                  'total_tva' =>"0.00000000",
                                   "total_ttc" =>"0.00000000",
                                   "paye"=>"1",
                                   "lines"=>$data_product,
                                  
                                  
                                 ];
                            
                                    // insert dans base de donnees historiquesidcommandes
                                    $date = date('Y-m-d');
                                    $historique = new Commandeid();
                                    $historique->id_commande = $donnees['id'];
                                    $historique->date = $date;
                                    // insert to
                                    $historique->save();
                            }
                            else{
                               $data_tiers =[];

                            }
                 
                             // recupérer les id_commande deja pris
                             if($this->testing($key_commande,$donnees['id'])==true)
                             {
                                 $id_commande_existe[] = $donnees['id'];
                             }
                  
                       }
   
                        // recupérer les deux variable dans les seter
                        $this->setCountd($orders_distributeur);// recupérer le tableau distributeur la variale.
                        $this->setCountc($orders_d);// recupérer le tableau des id commande non distributeur
                        // filtrer les doublons du tableau
                        $id_commande_exist = array_unique($id_commande_existe);
                         // recupérer le tableau
                        $this->setDataidcommande($id_commande_exist);
                        // renvoyer un tableau unique par tiers en fonction socid.
                        // données des non distributeurs....
                        $temp = array_unique(array_column($data_lines, 'socid'));
                        $unique_arr = array_intersect_key($data_lines, $temp);
          
                      // Filtrer les produits associés au tiers (socid = ref_ext simulé) suprimer en cas d'inégalité du tableau.
                      // clients invoices non distributeur 
                      foreach($unique_arr as $r => $val)
                      {
                          foreach($val['lines'] as $q => $vak)
                          {
                            if($val['socid']!=$vak['ref_ext'])
                            {
                              unset($unique_arr[$r]['lines'][$q]);// filtrer les produit qui n'appartienne pas à l'utilisateur
                            }
                          }
                      }

                  
                     foreach($data_tiers as $data)
                     {
                         // insérer les données tiers dans dolibar...
                         $this->api->CallAPI("POST", $apiKey, $apiUrl."thirdparties", json_encode($data));
                     }

                      foreach($unique_arr as $donnes)
                      {
                        // construire la 1 ère couche de facture dans dolibar
                        $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices", json_encode($donnes));
                      }
                       // activer le statut payé et lié les paiments  sur les factures.
                       $this->invoicespays();

                        dump($unique_arr);
                        dd('succes of opération');
                        // initialiser un array recuperer les ref client.
                        return view('apidolibar');
              
            }

            public function invoicespays()
            {
                $id =73777;
                $order = $this->getdataorderid($id);// pour une seul commande. retour de réponse tableau. $order
                // recuperer les données api dolibar.
                // recuperer les données api dolibar copie projet tranfer x.
                 $method = "GET";
                 $apiKey = "0lu0P9l4gx9H9hV4G7aUIYgaJQ2UCf3a";
                 $apiUrl = "https://www.transfertx.elyamaje.com/api/index.php/";
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
               foreach($invoices_id as $vk)
               {
                 $inv = (int)$vk['id'];
               }
               // recupérer le premier id de la facture
               foreach($invoices_asc as $vks)
               {
                  $inc = $vks['id'];
               }
 
               foreach($clientSearch as $data)
               {
                 $tiers_ref = $data['id'];
               }
      
              // le nombre recupérer 
              $count_datas = $order; // retour array ici
              $ids_orders =[];// recupérer les id commande venant de woocomerce
              $data_ids=[];// recupérer les nouveaux ids de commande jamais utilisés
         
             foreach($count_datas as $k =>$valis)
             {
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
           if($nombre_orders == 0)
           {
              $label = "Aucune commande transférée";
           }
           elseif($nombre_orders ==1)
           {
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
       
          
        for($i=$nombre_count; $i<$inv+2; $i++)
        {
           $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$i."/validate", json_encode($newCommandeValider));
        }
   
          // Lier les factures dolibar  à un moyen de paiement et bank.
        for($i=$nombre_count; $i<$inv+2; $i++)
        {
            $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$i."/payments", json_encode($newbank));
        }

           // mettre le statut en payé dans la facture  dolibar
        for($i=$nombre_count; $i<$inv+2; $i++)
        {
          $this->api->CallAPI("PUT", $apiKey, $apiUrl."invoices/".$i, json_encode($newCommandepaye));
        }


   }




























     
     
     public function invoicesdistributeur($date_after,$date_before)
     {
	         // recuperer les données api dolibar.
	         // recuperer les données api dolibar copie projet tranfer x.
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
	   
          // recupération du dernier id invoices dolibar
		    foreach($invoices_id as $vk)
	      {
		       $inv = $vk['id'];
	      }
         // le nombre recupérer  sur woocommerce.
	      $count_datas = $this->getDataorder($date_after,$date_before);
	      $ids_orders =[];// recupérer les id commande venant de woocomerce
	       $data_ids=[];// recupérer les nouveaux ids de commande jamais utilisés
	   
	      foreach($count_datas as $k =>$valis)
	      {
		       foreach($valis as $val)
		       {
			 
              if(in_array($val['status'],$data_status))
			        {
				        $ids_orders[] = $val['id'];// recupérer les bon id en fonction de la commande.
			  
			        }
		       }
		  
		  
		      if(!in_array($val['id'],$this->getDataidcommande()))
		      {
			      $data_ids[]= $val['id'];// recupérer les nouveaux id de commande.
		     }
	   }
	   
	   
	         // le nombre de facture à traiter en payé
	        $count_data = count($ids_orders);
	 
	        // recupérer le nombre de commande recupérer 
	         $nombre1 = $count_data;
	        $nombre2= count($this->getDataidcommande());// compter les anciennes ids 
	        // nombre des nouveaux order recupérer journaliier.
	        $nombre_orders = count($data_ids);
	        // tranformer le tableau en chaine de caractère
	        $list_id_commande = implode(',',$data_ids);
	        $nombre_count = $inv - count($nombre_orders)+1;
	        $datetime = date('d-m-Y H:i:s');
	        $dat = date('Y-m-d H:i:s');
	 
	        // insert infos dans bdd ...
	        if($nombre_orders == 0)
	        { 
            $label = "Aucune commande distribiteur transférée";
	        }
	        elseif($nombre_orders ==1)
	        {
		         $label ='Une commande distributeur transférée dans dolibars le '.$datetime.'';
	        }
	       else{
		         $label = $nombre_orders.' commandes distributeurs transférées dans dolibars le $datetime';
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
      // status impayés pour distributeur
	     $newCommandepaye = [
	     "paye"	=> 1,
	     "statut"	=> 1,
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

    // valider les fact dans dolibar
	   for($i=$nombre_count; $i<$inv+2; $i++)
	   {
		  $this->api->CallAPI("POST", $apiKey, $apiUrl."invoices/".$i."/validate", json_encode($newCommandeValider));
	   }

		  // mettre le statut impayé chez dolibar pour les distributeur
	   for($i=$nombre_count; $i<$inv+2; $i++)
	   {
		 $this->api->CallAPI("PUT", $apiKey, $apiUrl."invoices/".$i, json_encode($newCommandepaye));
	   }
	   
	   
	   // inserrer dans ma bb les id de commande distributeur.
	   // recupérer les id de facture dsitributeur.
	   for($i=$nombre_cout; $i< $inv+2; $i++)
	   {
	       $invoice =  new Invoicesdistributeur();
	       $invoice->invoice_id = $i;
	       $invoice->date = $date('d-m-Y H:i:s');
	       $invoice->status = "facture impayée";
	   }

    }
	

  }
     
    




