<?php

namespace App\Http\Service\Api;

use PDO;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class PdoDolibarr
{

    private $host;
    private $dbname;
    private $user; 
    private $password;
    private $dsn;
    private $pdo;

    public function __construct($host,$dbname,$user,$password){
        $this->host = $host;
        $this->dbname = $dbname;
        $this->user = $user;
        $this->password = $password;
        $this->pdo = new \PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    }

    function getCategories($fk_categorie = null){

        if ($fk_categorie) {
            $sql = 'SELECT `fk_product` FROM `llxyq_categorie_product` WHERE `fk_categorie` =' . $fk_categorie;
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':fk_categorie', $fk_categorie, PDO::PARAM_STR);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }else {

            $sql = 'SELECT * FROM `llxyq_categorie_product`';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $res;

    }

    function getReelFacturesByCategories($ids_gel, $with_dat){


        $sql = 'SELECT `facdet`.`fk_facture` 
        FROM `llxyq_facturedet` `facdet` LEFT JOIN `llxyq_facture` `fac` ON `facdet`.`fk_facture` = `fac`.`rowid` 
        WHERE `fk_product` IN ('. implode(",",$ids_gel).') AND `fac`.`total_ttc` > 0 
        AND `fac`.`paye` = 1';

        $groupr_by = ' GROUP BY `facdet`.`fk_facture`';
        $sql = $sql . $with_dat .$groupr_by ;
        

        $stmt = $this->pdo->prepare($sql);
        // Attribuer les valeurs aux paramètres pour eviter les injections sql
        foreach ($ids_gel as $index => $id) {
            $stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
        }

        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
        return $res;



    }

    function getFk_facture($with_dat){

        $sql = 'SELECT `facdet`.`fk_facture` 
        FROM `llxyq_facturedet` `facdet` LEFT JOIN `llxyq_facture` `fac` ON `facdet`.`fk_facture` = `fac`.`rowid` 
        WHERE `fac`.`total_ttc` > 0 AND `fac`.`paye` = 1' ;
        $groupr_by = ' GROUP BY `facdet`.`fk_facture`';
        $sql = $sql . $with_dat .$groupr_by ;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
        return $res;


    }

    function getClientPros($fks_facture, $with_dat){

        $sql = 'SELECT `fk_soc` 
        FROM `llxyq_facture` `facdet`
        WHERE `total_ttc` > 0 AND `paye` = 1 AND `rowid` IN  ('. implode(",",$fks_facture).')';
        $groupr_by = ' GROUP BY `fk_soc`';
        $sql = $sql . $with_dat .$groupr_by ;


        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
        return $res;


    }

    function getAllClientInHavingFacture($with_dat){

        $sql = 'SELECT `fk_soc` 
        FROM `llxyq_facture` `facdet`
        WHERE `total_ttc` > 0 AND `paye` = 1';
        $groupr_by = ' GROUP BY `fk_soc`';
        $sql = $sql . $with_dat .$groupr_by ;



        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $res;


    }

    function getCategoriesDolibarr($fk_categorie = null){

        if ($fk_categorie) {
            $sql = 'SELECT * FROM `llxyq_categorie` WHERE `fk_categorie` =' . $fk_categorie;
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':rowid', $fk_categorie, PDO::PARAM_STR);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }else {

            $sql = 'SELECT `rowid` as `id`, `fk_parent`, `label` FROM `llxyq_categorie`';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
       
 
        return $res;

    }

    function getProductsAssociations($fk_product_fils = null){

        if ($fk_product_fils) {
            $sql = 'SELECT * FROM `llxyq_product_association` WHERE `fk_product_fils` =' . $fk_product_fils;
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':rowid', $fk_product_fils, PDO::PARAM_STR);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }else {

            $sql = 'SELECT `rowid` as `id`, `fk_product_pere`, `fk_product_fils`, `qty` FROM `llxyq_product_association`';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
       
 
        return $res;

    }

    function getProducts($rowid = null){

        if ($fk_product_fils) {
            $sql = 'SELECT * FROM `llxyq_product` WHERE `rowid` =' . $rowid;
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':rowid', $fk_product_fils, PDO::PARAM_STR);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }else {

            $sql = 'SELECT `rowid` as `product_id`, `label`, `price_ttc`, `barcode`, `qty`, `qty`, `qty` FROM `llxyq_product_association`';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
       
 
        return $res;

    }

    function getStockAlerteMin($entrepot_destination){



        $sql = 'SELECT fk_product, fk_entrepot, seuil_stock_alerte, desiredstock FROM llxyq_product_warehouse_properties WHERE fk_entrepot = :entrepot_destination';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':entrepot_destination', $entrepot_destination, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Créer un tableau associatif indexé par la colonne 'id'
        $indexed_result = [];
        foreach ($res as $row) {
            $indexed_result[$row['fk_product']] = $row;
        }
        
        return $indexed_result;
        

    }

    
    function getStockProductByEntrepot($entrepot_destination){



        $sql = 'SELECT fk_product, reel FROM llxyq_product_stock WHERE fk_entrepot = :entrepot';
        
        $stmt = $this->pdo->prepare($sql);


        $stmt->bindParam(':entrepot', $entrepot_destination, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $indexed_result = [];
        foreach ($res as $row) {
            $indexed_result[$row['fk_product']] = $row;
        }

               
        return $indexed_result;
        

    }

    function getAllProduct(){



        $sql = 'SELECT rowid AS product_id,label,barcode FROM llxyq_product';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

       

        
        $indexed_result = [];
        foreach ($res as $index => $row) {
            
            $indexed_result[$row['product_id']] = $row;
        }
              
        return $indexed_result;
        

    }

    function getAllEntrepot(){


        $sql = 'SELECT rowid AS id_entrepot, ref AS name_entrepot FROM llxyq_entrepot WHERE rowid IN(15,6)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

       

        
        $indexed_result = [];
        foreach ($res as $index => $row) {


            $res[$index]["name_entrepot"] = utf8_encode($row["name_entrepot"]);

            $indexed_result[$row['id_entrepot']] = $res[$index];
        }
              
        return $indexed_result;
        

    }




}







