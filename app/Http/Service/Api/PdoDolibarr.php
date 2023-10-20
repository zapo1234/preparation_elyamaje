<?php

namespace App\Http\Service\Api;

use PDO;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class PdoDolibarr
{



    function dolibarrDbSql($sql){

        $host = env('HOST_ELYAMAJE'); 
        $dbname = env('DBNAME_DOLIBARR');
        $user = env('USER_DOLIBARR'); 
        $password = env('PW_DOLIBARR');

        $dsn = "mysql:host=$host;dbname=$dbname";
        $pdo = new \PDO($dsn, $user, $password);
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':ref_facture', $ref_facture, PDO::PARAM_STR);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
        return $res;

    }



}







