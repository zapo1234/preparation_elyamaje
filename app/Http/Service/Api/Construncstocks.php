<?php
namespace App\Http\Service\Api;

use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\ProductsCategorie;
use Automattique\WooCommerce\HttpClient\HttpClientException;
use PDO;

class  Construncstocks
{
    
      private $api;
      private $datas = [];

      private $data =[];

      private $rape =[];
   
      private $rapes =[];

      private $linesproduct =[];

      private $stocksproduct =[];

      private $stocksrape =[];

       public function __construct(
        Api $api
       )
       {
         $this->api=$api;
      
       }

     /**
   * @return array
    */
     public function getDatas(): array
     {
      return $this->datas;
     }
   
   
    public function setDatas(array $datas)
    {
     $this->datas = $datas;
     return $this;
    }


    /**
   * @return array
    */
    public function getData(): array
    {
     return $this->data;
    }
  
  
   public function setData(array $data)
   {
    $this->data = $data;
    return $this;
   }



    /**
   * @return array
    */
    public function getRape(): array
    {
      return $this->rape;
    }
  
   public function setRape(array $rape)
   {
     $this->rape = $rape;
      return $this;
   }


   
    /**
   * @return array
    */
    public function getRapes(): array
    {
      return $this->rapes;
    }
  
   public function setRapes(array $rapes)
   {
     $this->rapes = $rapes;
      return $this;
   }


     /**
   * @return array
    */
    public function getLinesproduct(): array
    {
     return $this->linesproduct;
    }
  
  
   public function setLinesproduct(array $linesproduct)
   {
    $this->linesproduct = $linesproduct;
    return $this;
   }


     /**
   * @return array
    */
    public function getStocksproduct(): array
    {
     return $this->stocksproduct;
    }
  
  
   public function setStocksproduct(array $stocksproduct)
   {
     $this->stocksproduct = $stocksproduct;
     return $this;
   }


     /**
   * @return array
    */
    public function getStocksrape(): array
    {
     return $this->stocksrape;
    }
  
  
   public function setStockrape(array $stocksrape)
   {
     $this->stocksrape = $stocksrape;
     return $this;
   }



    
     public  function Constructstocks()
     {

      // $data = DB::connection('mysql2')->select("SELECT fk_product_fils,fk_product_pere,qty  FROM llxyq_product_association");


      // $host = '109.234.162.138'; // nom d'hôte du serveur de la base de données
      // $dbname = 'mamo9937_doli54'; // nom de la base de données
      // $user = 'mamo9937_dolib54'; // nom d'utilisateur de la base de données
      // $password = ']14]1pSxvS'; // mot de passe de la base de données
     
      // $dsn = "mysql:host=$host;dbname=$dbname";

      // $pdo = new \PDO($dsn, $user, $password);

      // dd($pdo);

      // $sql = 'SELECT `fk_product_fils , fk_product_pere` FROM `llxyq_product_association`';

   
      // // Préparation de la requête
      // $stmt = $pdo->prepare($sql);

      // // Exécution de la requête avec les valeurs
      // $stmt->execute();

      // $res = $stmt->fetchAll();

      // dd($res);




























        //

         $apiKey = env('KEY_API_DOLIBAR'); 
         $apiUrl = env('KEY_API_URL');

         // recupérer les product associes

          // recupérer les informations des produits souhaités et crée un jeu de donnée.
        $listproduct = DB::table('products_dolibarr')->select('product_id','label','warehouse_array_list')->get();
        $list_product = json_encode($listproduct);
        $list_products = json_decode($listproduct,true);
        
      

        $data_product =[];
         $line_product =[];
        foreach($list_products as $valus){
          if($valus['product_id']!="6371"){
          $chainex = $valus['product_id'].'%'.$valus['label'].'%'.$valus['warehouse_array_list'];
          $data_product[$chainex] = $valus['product_id'];
           $line = $valus['product_id'].'%'.$valus['label'];
           $line_product[$line] = $valus['product_id'];
          }
      }
      
      
         // recupérer le tableau
            $this->setLinesproduct($line_product);
          // aller recupérer la table product association dolibar.
           // filtrer directement avec une requete sql depuis dolibar.
         $data = DB::connection('mysql2')->select("SELECT fk_product_fils,fk_product_pere,qty  FROM llxyq_product_association");
         $list_assoc = json_encode($data);
         $list_assocs = json_decode($list_assoc,true);


         $datas =[];
         foreach($list_assocs as $values){
              
          $datas[$values['fk_product_fils']][] =[
              'id_product_pere'=>$values['fk_product_pere'],
              'coeff_qte'=> $values['qty']
             ];
        }


         // je veux les produit qui ont deux inden dans les array
         $data_donnees =[];
         foreach($datas as $key =>$valus){
          
              if(count($valus)==1 OR count($valus)==2 OR count($valus)==4 OR count($valus)==3){
                 $data_donnees[$key] =$valus;
              }
           }
           
          
           $tab_result_array2 =[];
            
          // aller construire les données souhaite.
           $list_data_assoc_produit =[];
           $recup_id_datas =[];// recupérer les ids data pour construire les mise a jours.
           foreach($data_donnees as $keys =>$values){
          
               foreach($values as $val){
               // recupérer le product et son id.
                 $libelle_product = array_search($keys,$data_product);
                  if($libelle_product!=false){
                    $libel_product = explode('%',$libelle_product);
                     $index_product = $libel_product[1].'%'.$libel_product[2].'%'.$keys;
                   // recupérer les d
                   // gerer les fj_pere des lot 
                   $libelle_pere_product = array_search($val['id_product_pere'],$data_product);
                  if($libelle_pere_product!=false){
                     $libelle_pere_products = explode('%',$libelle_pere_product);
                      $list_data_assoc_produit[$index_product][] =  [
                                     'id_product_pere'=> $val['id_product_pere'],
                                     'libelle_family'=> $libelle_pere_products[1],
                                     'quantite'=> $libelle_pere_products[2],
                                     'coeff_qte'=> $val['coeff_qte']
                                   
                    ];

                    // recupérer ici
                    

               }

             }

           }

          }

             // filtrer en fonction des limes .
              $limes ="Lime";
              $data_product_array_choix =[];
              $tab_result_array = [];
              $data_limes =[];
              $data_details_limes =[];
              $tab_result_array2 =[];
              $tab_result_array1 =[];
              
              foreach($list_data_assoc_produit as $kel =>$valus){

                   $chaine_index = explode(' ',$kel);
                   
                    if($chaine_index[0]=="Bloc"){
                       
                       $index_prefix ="Bloc blanc";
                         
                        $tab_result_array2[$index_prefix][] = [
                           $kel =>$valus
                        ];
                      
                   }
                   
                   
                    if($chaine_index[0]=="Bâtonnet"){
                       
                       $index_prefix ="Bâtonnet de Buis";
                         
                        $tab_result_array1[$index_prefix][] = [
                           $kel =>$valus
                        ];
                      
                   }
                 
                   if($chaine_index[0]=="RAPE"){
                       $index_prefix ="RAPE MANCHE";
                       $tab_result_arrays1[$index_prefix][] = [
                        $kel =>[]
                     ];

                     // chaine index ..
                     $array_tab[$index_prefix][] =[
                      $kel => $valus
                     ];
                   
                }

                 if($chaine_index[0]=="Plaque"){
                  $index_prefix ="Plaque Râpante";
                  $tab_result_arrays21[$index_prefix][] = [
                   $kel =>$valus
                ];

                   // recupérer la ligne une pour id 
                   // chaine index ..
                    $array_tab1[$index_prefix][] =[
                     $kel => $valus
                   ];
                }

                
                    if($chaine_index[0]=="Lime"){
                        $data_product_array_choix[$kel] =$valus;
                        // construire la sortir de mon tableau
                        $index_prefix = $chaine_index[0].' '.$chaine_index[1];
                        
                        $tab_result_arrays[$index_prefix][] = [
                           $kel =>$valus
                        ];
                      
                       $recap_id[] = $valus;
                       // recupérer les limes 
                        $data_limes[] =$kel;
                        
                      
                   }
                   
               }

               // recupérer les raps.
               $array_result_rap = array_merge($tab_result_arrays1,$tab_result_arrays21);
               $this->setRape($array_result_rap);
              //
            // reconstruire le tableau....
             $tab_result_array = array_merge($tab_result_arrays,$tab_result_array1,$tab_result_array2);
        
              $array_reverse = array_reverse($tab_result_array);
    

          //unset($array_reverse['Lime Droite']);
            $recap_ids_line =[];// recupérer les lines souhaites
            // recu pere les ids en line constituf du tableau.

            // les raps et plaquante
            $recap_ids_lines =[];

            $list_product_limite_stocks =[];

            $list_product_limite_rape =[];
        
            foreach($array_reverse as $valus){
              foreach($valus as $sm => $valis){
              
                  foreach($valis as $lmm => $valo){
                     // recupérer le id_parent haut.
                    $index_libel = explode('%',$lmm);
                     
                    if((int)$index_libel[1] < 10){
                        $list_product_limite_stocks[] =[
                          'produit'=>$index_libel[0]
                        ];
                    }
                    // recupérer les produit en unite qui sont moins de 10
                      foreach($valo as $ll){
                       // recupérer le taux chez le libelle..
                         $taux_libelle = explode(' ',$ll['libelle_family']);

                         $recap_ids_line[]= $ll['coeff_qte'].'%'.$ll['id_product_pere'].'%'.$index_libel[2].'%'.$index_libel[1];

                  }
                }
           }
          }
           
           
          // recupérer les produit faible ens tocks moins de 10
           $this->setStocksproduct($list_product_limite_stocks);
           
          $rape_index_first =[];// crée le 1 er index
          $plaque_index_first =[];// crée le 1 er index
          foreach($array_tab as $kd=>$vals){
             foreach($vals[0] as $ml=>$vbm){
              $rape_index_first[] = $ml;
             }
          }

          foreach($array_tab1 as $kd=>$vals){
            foreach($vals[0] as $mls=>$vbm){
             $plaque_index_first[] = $mls;
            }
         }

          // traiter les rap et plaquete.
          foreach($array_result_rap as $valus){
            foreach($valus as $sm => $valis){
            
                foreach($valis as $lmm => $valo){
                   // recupérer le id_parent haut.
                  $index_libel = explode('%',$lmm);
                  
                  if((int)$index_libel[1] < 10){
                    $list_product_limite_rape[] = [
                       'produit'=>$index_libel[0]
                    ];
                }

                    foreach($valo as $ll){
                     // recupérer le taux chez le libelle
                       $taux_libelle = explode(' ',$ll['libelle_family']);
                       $recap_ids_lines[]= $ll['coeff_qte'].'%'.$ll['id_product_pere'].'%'.$index_libel[2].'%'.$index_libel[1];
                }
            }
          }
        }
        
        // recupérer le tableau de stock faible des rapes
        $this->setStockrape($list_product_limite_rape);

         $recap_ids_lines =  array_merge($rape_index_first,$plaque_index_first,$recap_ids_lines);

          // ici .
          $this->setRapes($recap_ids_lines);
        
          $this->setdata($recap_ids_line);

          //dd($array_reverse);
          
         return $array_reverse;
         
    }


      public function listcategories()
      {
    
             $method = "GET";
             // recupérer les clé Api dolibar transfertx........
              $apiKey = env('KEY_API_DOLIBAR'); 
              $apiUrl = env('KEY_API_URL');
        
              // recupérer les appels via dolibar directement.
             $data = DB::connection('mysql2')->select("SELECT rowid,label,barcode,price_ttc FROM llxyq_product");
              $name_list = json_encode($data);
              $list_product = json_decode($name_list,true);

              
              $list_products =[];
              foreach($list_product as $vak){
                   $list_products[] = [
                   'id'=> $vak['rowid'],
                  'label'=>$vak['label'],
                  'price_ttc'=>$vak['price_ttc'],
                  'barcode'=> $vak['barcode']
                ];
        
              }
        
                $data1 = DB::connection('mysql2')->select("SELECT rowid,label,fk_parent FROM llxyq_categorie");
               $name_list1 = json_encode($data1);
               $list_categori = json_decode($name_list1,true);
               $list_categorie =[];
              foreach($list_categori as $vad){
                   $list_categorie[] = [
                   'id'=> $vad['rowid'],
                  'label'=>$vad['label'],
                  'fk_parent'=>$vad['fk_parent']
                ];
        
              }
        
          
        
            // recupérer les product directement depuis dolibar  via Api.
           /*   $produitParam = ["limit" => 1400, "sortfield" => "rowid"];
               $listproduct = $this->api->CallAPI("GET", $apiKey, $apiUrl."products", $produitParam);
               // reference ref_client dans dolibar
              $list_products = json_decode($listproduct, true);// la liste des produits dans dolibarr
        
            // recupérer les categoris directement depuis l'api
              $produitParam = ["limit" => 120, "sortfield" => "rowid"];
              $categorie = $this->api->CallAPI("GET", $apiKey, $apiUrl."categories", $produitParam);
              // reference ref_client dans dolibar
              $list_categorie = json_decode($categorie, true);// la liste des produits dans dolibarr
           */
            
         //grouper dans 1 er temps la table de jointure entre les categories et product dolibarr.
             /*  $list = DB::table('categories_dolibarr')->select('id','label','fk_parent')->get();
               $list_categorie = json_encode($list);
               $list_categorie = json_decode($list,true);
              */
               $data_categories =[];
               $data_parent =[];
               
        
              foreach($list_categorie as $value){
                 $chaine = $value['id'].','.$value['label'];// formalisé une chaine de données
                 $chaine_d = $value['id'].','.$value['fk_parent'];
                 $data_categories[$chaine] = $value['id'];
                 $data_parent[$chaine_d]= $value['id'];
                 $u[] =  $value['fk_parent'];
                 $y[$value['fk_parent']][] =[
                        'id'=>$value['id'],
                      'label'=>$value['label']
                 ];
                  
                 if($value['fk_parent']!=0){
                    $j[] = $value['id'];
        
                 }
        
                 
              }
            
             // recupérer les informations des produits souhaités et crée un jeu de donnée.
             /*   $listproduct = DB::table('products_dolibarr')->select('product_id','label','price_ttc','barcode')->get();
                $list_product = json_encode($listproduct);
                $list_products = json_decode($listproduct,true);
        
            */
                $data_product =[];
                foreach($list_products as $valus){
                  $chainex = $valus['id'].'%'.$valus['label'].'%'.$valus['price_ttc'].'%'.$valus['barcode'];
                  $data_product[$chainex] = $valus['id'];
              }
        
              // recupérer les images des product dans (crée un jeu de donnée... pour les recupèré)..
               $listimg = DB::table('products')->select('barcode','image')->get();
               $list_img = json_encode($listimg);
               $list_img = json_decode($listimg,true);
               $data_img =[];
               foreach($list_img as $valc){
                 $chainey = $valc['barcode'].','.$valc['image'];
                 $data_img[$chainey] = $valc['barcode'];
        
              }
                //grouper la categories id et les id_product
                $list_join= ProductsCategorie::query()
                ->get()
                ->groupBy('fk_categorie');
        
                 $list_joins = json_encode($list_join);
                 $list_joins = json_decode($list_join,true);
        
        
                 // filtrer directement avec une requete sql depuis dolibar.
                /* $data3 = DB::connection('mysql2')->select("SELECT fk_categorie,fk_product FROM llxyq_categorie_product GROUP BY fk_categorie");
                 $list_join = json_encode($date3);
                 $list_joins = json_decode($list_join,true);
               */
                
                // créer le jeu de données souhaités à recupéré.
               function groupByTile($list_categorie){
                 $final_array =[];
                  foreach($list_categorie as $key=>$valc){
                      $final_array[$valc['fk_parent']][] = $valc;
                    }
                  return $final_array;  
               }
        
                // defini une fonction pour regrouper les categoris via leur key(categoris enfants)
                function groupByTiles($list_categories){
                   $final_array =[];
                   foreach($list_categories as $key=>$valc){
                     $final_array[$valc['sous_categoris']][] = $valc;
                    }
                    return $final_array;  
                 }
                
                  $list_cat = groupByTile($list_categorie);// grouper les categoris par key(categoris).
        
                  
                  // regrouper les sous categoris.
                  // boucler et grouper en fonction...
                   $datax = $list_cat[0];// recupoére les produit qui n'ont pas de categoris enfants.
                    unset($list_cat[0]);// filtrer les categoris qui n'ont pas d'enfant().
        
                   $datay = $list_cat;
                   $key_cat =[];// recupérer les keys qui constitue la liste des enfant dans ce tableau.
                   $array_id =array_keys($list_cat);
                 
                  // filtrer les doublons dans l'apparaution de categoris enfants.
                    foreach($datax as $vals){
                       if(!in_array($vals['id'],$array_id)){
                       $data_list_no_cat[$vals['id']][] =[
                        "id" => $vals['id'],
                        "label" => $vals['label'],
                        "fk_parent" => 0   
                      ];
                   }
                 }
                
        
                   foreach($datay as $kd => $valt){
                       foreach($valt as $ls){
                           if(!isset($datay[$ls['id']])){
                              $product =[];
                         }else{
                              $product = $datay[$ls['id']];
                        }
                         // traiter les categoris dans les sous.
                         $xyx[$kd][] =[
                         'id'=>$ls['id'],
                         'label'=>$ls['label'],
                         'fk_parent'=> $kd,
                         'categoris_assoc'=>$product
                       ];
                     }
                   }
                
                   // traite les produit avec n'ayant pas de categoris enfant.
                    foreach($data_list_no_cat as $kls => $val){
                        foreach($val as $vlis){
                           foreach($list_joins as $kj=>$valo){
                             foreach($valo as $vbn){
                                $product_data = array_search($vbn['fk_product'],$data_product);
                                 if($product_data!=false){
                                   $product_datas = explode('%',$product_data);
                                
        
                                if($kj == $vlis['id']){
                                   // recupérer le libelle de la categoris
                                   $fk_cat_parent = $vlis['id'];
                                   $label_search = array_search($fk_cat_parent,$data_categories);
                                 if($label_search!=false){
                                   $label = explode(',',$label_search);
                               }
        
                               // recupérer le chemin de l'image associé.
                               $search_img = array_search($product_datas[3],$data_img);
                               if($search_img!=false){
                                  $s_img = explode(',',$search_img);
                                  $img =$s_img[1];
                               }else{
                                  $img="";
                               }
        
                                 $xy = $kj.','.$label[1];
                                 $result_list_cats[$label[1]][] =[
                                  'product'=>[
                                        'id_product'=>$product_datas[0],
                                        'label'=>$product_datas[1],
                                        'prix_ttc' => $product_datas[2],
                                        'barcode' => $product_datas[3],
                                       ]
                                  ];
                              }
                           }
                          }
                       }
                   }
                 }
                 
                     // traiter les produits provenant d'une categoris mère
                      // balyés les accasoirre electrique, 
                    
                        foreach($datay as $keys => $valis){
                          foreach($valis as $valc){
                            foreach($list_joins as $key => $values){
                              foreach($values as $vbn){
                               $product_data = array_search($vbn['fk_product'],$data_product);
                                if($product_data!=false){
                                  $product_datas = explode('%',$product_data);
                               
                               
                                 if($key == $valc['id']){
                                 // recupérer le libelle
                                  $fk_cat_parent = $valc['fk_parent'];
                                  $label_search = array_search($fk_cat_parent,$data_categories);
                                     if($label_search!=false){
                                     $label = explode(',',$label_search);
                                   }
                                   // recupérer l'imaage associé.
                                  // recupérer le chemin de l'image associé.
                                 $search_img = array_search($product_datas[3],$data_img);
                                  if($search_img!=false){
                                    $s_img = explode(',',$search_img);
                                   $img =$s_img[1];
                                  }else{
                                     $img="";
                                  }
                                
                                 $xy = $keys.','.$label[1];
                                $key_int = $label[1].','.$valc['id'];
                                $product =[
                                'id_product'=>$product_datas[0],
                                'label'=>$product_datas[1],
                               'prix_ttc' => $product_datas[2],
                               'barcode' => $product_datas[3]
                              ];
                               $result_list_cat[$label[1]][] =[
                                'sous_categoris'=>$valc['label'],
                                'product'=>[
                                      'id_product'=>$product_datas[0],
                                      'label'=>$product_datas[1],
                                     'prix_ttc' => $product_datas[2],
                                     'barcode' => $product_datas[3]
              
                                  ]
                                ];
                            }
                          }
                         }
                        }
                     }
                 }
                  
                  // regrouper les categoris en fonction des catzegoris mère en fonction de la key array.
                 $resultat_categoris_mere =[];
                 
                 
                     foreach($result_list_cat as $kl => $valus){
                       $resultat_categoris_mere[$kl][] =[
                                  groupByTiles($valus),
                          ];
                    }
                    // 
        
                  
                     $array_text_exp = array();
                     $text ="Accessoire";// choix de personnalisé notre affichage(Attention a des modif un cas au harsard)
                     $key_text="Paillette";
                     $result_final_categoris_mere =[];
                     $retrait_accesoir =[];
        
                     foreach($resultat_categoris_mere as $kt =>$values){
                         if(!in_array($kt,$array_text_exp)){
                            $result_final_categoris_mere[$kt]=$values;
                         }
        
                       if($kt==$text){
                          $retrait_accesoir[$key_text] = $values[0][0]['Paillette'];
                       }
                    }
        
                    $data_finale = array_merge($retrait_accesoir,$result_final_categoris_mere);
        
                     // traiter dynamiqumement le tableau.
                    foreach($result_final_categoris_mere as $key =>$valis){
                       $key_data[] = $key;
                      
                    }
                  
                     $recher_key = [];// les key du composant des categoris (enfant à détécter)
                     // grouper les sous sous categoris enfant et mère...
                    $data_result =[];

                    //dd($result_final_categoris_mere['Base & Prep Elya Maje'][0][0]['Base']);
                     
                    // travailler sur les accesoire.n'etant pas dans une sous sous categoris
                    // Accesoir onglerie
                    $array_cat_onglerie[] = [
                      'Autre' => $result_final_categoris_mere['Accessoire'][0][0]['Accessoire Onglerie']// les produit dans accesoire qui ont pas une sous categorie
                    ];
        
                 // Accesoire electrique
                 $array_cat_electrique[] = [
                  'Autre' => $result_final_categoris_mere['Accessoire'][0][0]['Accessoire Electrique']// les produit dans accesoire qui ont pas une sous categorie
                 ];
        
               // recupere les base colorées n'etant pas dans des categories
              
                    
                    foreach($result_final_categoris_mere as $kl =>$vl){
                           foreach($vl as $vs){
                                 foreach($vs as $kc => $l){
                                     foreach($l as $m =>$h){// construire le tableau.
                                          if($m!==0){
                                            
                                             if($kl!=="Accessoire"){
                                              if(in_array($m,$key_data)){
                                                 if($m==="Base"){
                                                    
                                                  if(isset($data_finale['Base Color?e'])){
                                                      $vk = $data_finale['Base Color?e'][0]; // NB ne pas modifie les text dans dolibar
                                                    } else {
                                                      $vk = $data_finale['Base Colorée'][0]; // NB ne pas modifie les text dans dolibar
                                                    }
                                                  
                                                    foreach($vk[0] as $key =>$l){
                                                        $xx[]=[
                                                          $key => $l
                                                       ];
                                                    }
                                                      $vk = $xx;
                                                      
                                                   }else{
                                                          $vk = $result_final_categoris_mere[$m][0];
                                                      
                                                     }
                                                $recher_key[] = $m;// les sous categoris enfant possedant une clé.
        
                                           }else{
                                               $vk = $h;
                                          }
                                        }
                                      }else{
                                          $vk = $h;
                                       }
        
                                        // construire son tableau..
                                          $resultat[$kl][] =[
                                              $m => $vk
                                               
                                          ];
                                  }
                              }
                         }
                    }
        
                  
                    $resultat = array_merge($retrait_accesoir,$resultat);
         
                     foreach($recher_key as $value){
                        unset($resultat[$value]);
                     }
        
                      // recupérer les Accesoire onglerie et les Acessoire electrique
                    $array_acc_onglerie = $resultat['Accessoire Onglerie'];
                    $array_acc_elec = $resultat['Accessoire Electrique'];
                  
                   // merge les tableau ci desous avec le tableau des autre
                   $recap_array_onglerie['Accessoire Onglerie'] = array_merge($array_acc_onglerie,$array_cat_onglerie);
                   $recap_array_electricite['Accessoire Electrique'] = array_merge($array_acc_elec,$array_cat_electrique);
        
                   //dump($recap_array_onglerie);
        
                   unset($resultat['Accessoire Onglerie']);
                   unset($resultat['Accessoire Electrique']);
                   
                  
                   $resultats = array_merge($recap_array_electricite,$recap_array_onglerie,$resultat);
                   //dd($resultats['Accessoire Electrique']);
                  // afficher les categeoris souhaités.
                      // recupérer le tableau pour les produit qui n'ont pas de categoris enfants.
                      $this->setDatas($result_list_cats);

                      dd($resultats);
                      
                      return $resultats;
                      //recupérer le tableau pour les produit qui n'ont pas de categoris enfants...
                      // $this->setDatas($result_list_cats);
                    
             
            } 

            function updateinitialQtyLotToZero(){

              $ids_rapes = [5466,5467,5469,5468];

              try {
                  $res = DB::table('products_dolibarr')
                  ->Join('products_association', 'products_dolibarr.product_id', '=', 'products_association.fk_product_pere')
                  ->where('products_association.qty', 10)
                  ->orWhere('products_association.qty', 5)
                  ->orWhereIn('products_dolibarr.product_id', $ids_rapes)
                  ->update(['products_dolibarr.warehouse_array_list' => 0]);
                  ;
                  return ["success" => true, "message" => "Les quantités des kits on été mise a zéro"];
              
              } catch (\Throwable $th) {
                  return ["success" => false, "message" => $th->getMessage()];
              }
      
          
          }

          public function updateProductsCaisse()
          {
              try {
                  $url = 'https://www.poserp.elyamaje.com/api/index.php/';
                  $key = 'VA05eq187SAKUm4h4I4x8sofCQ7jsHQd';
      
                  $parametres = array(
                      'apikey' => $key,
                      'limit' => 10000,
                  );
      
                  $products = $this->api->CallAPI("GET", $key, $url."products",$parametres);
                  $products = json_decode($products,true);
      
                  $array_final = array();
      
                  foreach ($products as $key => $prod) {
                      if ($prod["status"] == 1) {   
      
                          $qte = 0;  
      
                          if ($prod["warehouse_array_list"]) {
                              foreach ($prod["warehouse_array_list"][$prod["id"]] as $key => $value) {
                                  if ($value["warehouse"] == "Entrepôt Malpassé") {
                                      if ($qte == 0) {
                                          $qte = $value["stock"];
                                      }
                                  }
                              }
                          }
      
                          $data = [
                              "product_id" => $prod["id"],
                              "label" => $prod["label"],
                              "price_ttc" => $prod["price"]? ($prod["price"]*(($prod["tva_tx"]*0.01)+1)):$prod["price_ttc"],
                              "barcode" => $prod["barcode"],
                              "poids" => $prod["weight"]?? 0,
                              "warehouse_array_list" => $qte,
                          ];
                          array_push($array_final,$data);
                      }
                  }
      
                  // dd($array_final);
      
                  if ($array_final) {
                      DB::beginTransaction();
                     
                      DB::table('products_dolibarr')->truncate();
                      DB::table('products_dolibarr')->insert($array_final);
      
                      DB::commit();
      
                      return ["success" => true, "message" => "La table produit dolibarr a bien été mise à jour"];
                  }else {
                      return ["success" => false, "message" => "Aucune donnée récupéré depuis dolibarr production (La table n'a pas été mise à jour)"];
                  }
      
                  
                  
              } catch (\Throwable $th) {
                  DB::rollBack();
                  return ["success" => false, "message" => $th->getMessage()];
              }  
      
          }
        
           
           
     }


        
      

   
     
