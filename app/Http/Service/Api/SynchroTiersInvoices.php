<?php

namespace App\Http\Service\Api;

use PDO;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Repository\AccountWoocommerce\AccountWoocommerceRepository;

class SynchroTiersInvoices
{
    private $api;
    
    public function __construct(Api $api,
    AccountWoocommerceRepository $account)
    {
        // Constructeur
        $this->api = $api;
        $this->account = $account;
    }

    public function getEmailTiers($id_wc)
    {
       // recupérr l'email et le socid correspondant du tiers
       $result = $this->account->getUser($id_wc);// recupérer email
       
       return $result;

    }

   
}
