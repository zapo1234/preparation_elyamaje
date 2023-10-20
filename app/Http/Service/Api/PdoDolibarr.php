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

        $sql = 'SELECT `fk_product` FROM `llxyq_categorie_product` WHERE `fk_categorie` =' . $fk_categorie;
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':fk_categorie', $fk_categorie, PDO::PARAM_STR);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
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



}







