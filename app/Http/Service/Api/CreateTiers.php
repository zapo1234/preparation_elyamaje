<?php

namespace App\Http\Service\Api;

use PDO;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Repository\Tiers\TiersRepository;
use App\Repository\AccountWoocommerce\AccountWoocommerceRepository;
use App\Repository\Commandeids\CommandeidsRepository;
use App\Http\Service\Api\SynchroTiersInvoices;

class CreateTiers
{
    private $api;
    private $socidtiers;
    private $synchrotiers;
    

    public function __construct(Api $api, AccountWoocommerceRepository $account, 
    CommandeidsRepository $commande,
    TiersRepository $tiers,
    SynchroTiersInvoices $synchrotiers)
    {
        // Constructeur
        $this->api = $api;
        $this->account = $account;
        $this->commande = $commande;
        $this->tiers = $tiers;
        $this->synchrotiers = $synchrotiers;

        
    }

    public function getSocidtiers() {
        return $this->socidtiers;
      }
    
    
      public function setSocidtiers($socidtiers) {
        $this->socidtiers = $socidtiers;
        return $this;
      }
    

    /**
     * Crée un client pour la facturation dans Dolibarr.
     *
     * Cette méthode gère la création ou la mise à jour d'un client dans le système Dolibarr
     * à partir des informations fournies par WooCommerce. Elle vérifie d'abord si le client existe
     * déjà dans la base de données, puis crée un nouveau client si nécessaire.
     *
     * @param string $email L'adresse e-mail du client.
     * @param int $id_cusotmer L'identifiant unique du client dans WooCommerce.
     * @param string $phone Le numéro de téléphone du client.
     * @param string $city La ville où le client est situé.
     * @param string $adresse L'adresse du client.
     * @param int $order_id L'identifiant de la commande associée au client.
     * @param string $first_name Le prénom du client.
     * @param string $lastname Le nom de famille du client.
     *
     * @return array $data_tiers un tableau contenant les info du client.
     */
    public function createtiers($email, $id_cusotmer, $phone, $city, $zip,$adresse, $order_id, $first_name, $last_name,$date_created,$id_country,$code_country,
    $compagny,$is_professionel)
    {
        
        $list_tier = $this->tiers->getalltiers(); // recupérer les tiers à jours ..
        
        // recupérer l'id du pays du clients associé au préfixe du pays.
        $data_id_country = $this->commande->getIdcountry();
        $data_ids_country = [];
        foreach ($data_id_country as $valu) {
            $data_ids_country[$valu['rowid']] = $valu['code'];
        }

        // recupérer les email, socid, code client existant dans tiers
        $data_email = []; // entre le code_client et email.
        $data_list = []; // tableau associative de id et email
        $data_code = []; // tableau associative entre id (socid et le code client)
        $data_phone = [];
        
        foreach ($list_tier as $val) {
            if ($val['email'] != "") {
                $data_list[$val['socid']] = mb_strtolower($val['email']);
            }

            if ($val['phone'] != "") {
                $data_phone[$val['socid']] = $val['phone'];
            }

            // recuperer id customer du client et créer un tableau associative.
            $code_cl = explode('-', $val['code_client']);
            if (count($code_cl) > 2) {
                $code_cls = $code_cl[2];
                if ($code_cls != 0) {
                    $data_code[$val['socid']] = $code_cls;
                }
            }
        }

        $ref_client = rand(4, 10);
        $email_true = mb_strtolower($email);
    
        // recupérer id du tiers en fonction de son email...
        $fk_tiers = array_search($email_true, $data_list);
        
        $espace_phone = str_replace(' ', '', $phone); // suprimer les espace entre le phone
        $fk_tiers_phone = array_search($espace_phone, $data_phone);
        
        // recupérer id en fonction du customer id
        $fk_tier = array_search($id_cusotmer, $data_code);
        
        // convertir la date en format timestamp de la facture.
        $datetime = $date_created; // date recu de woocomerce.
        $date_recu = explode(' ', $datetime); // dolibar...
        // transformer la date en format date Y-m-d...
        $datex = $date_recu[0];
        $new_date = strtotime($datex); // convertir la date au format timestamp pour Api dolibarr.
        
        // gere le cas des anciens qui ont un code CU avant preparation.
        $id_wc = $id_cusotmer;
        $emailuser = $this->synchrotiers->getEmailTiers($id_wc); // email capter existant dans les bdd dolibar et wc.

        $socid="";
        
        if(count($emailuser) != 0) {
            $emailUser = mb_strtolower($emailuser[0]['email']);
            $fk_tiers_CU = array_search($emailUser, $data_list);
        } else {
            $fk_tiers_CU = "";
        }

        if($fk_tiers != "") {
            $socid = $fk_tiers;
        }

        if($fk_tiers_phone != "" && $fk_tiers == "" && $fk_tiers_CU == "") {
            $socid = $fk_tiers_phone;
        }

        // construire le tableau
        if ($fk_tier != "" && $fk_tiers == "" && $fk_tiers_phone == "" && $fk_tiers_CU == "") {
            $socid = $fk_tier;
            // recupérer dans la bdd en fonction du socid 
        }

        // Pour les anciens clients CU capter
        if ($fk_tiers_CU != "" && $fk_tier == "" && $fk_tiers == "" && $fk_tiers_phone == "") {
            $socid = $fk_tiers_CU;
            // recupérer dans la bdd en fonction du socid 
        }

        // faire l'ouverture pour les client CU non capter si le phone n'a pas changer donne lui
          if($fk_tiers_phone != "") {
            $socid = $fk_tiers_phone;
        }

       


        if ($socid != "") {
            $data = $this->tiers->gettiersid($socid);
            if (count($data) == 0) {
                $data_infos_user = [];
            } else {
                foreach ($data as $valu) {
                    $nom = $valu['nom'];
                    $email = $valu['email'];
                }
                $data_infos_user = [
                    'first_name' => $nom,
                    'last_name' => '',
                    'email' => $email,
                ];
            }
        }

         $data_tiers =[];// initialiser le array

        // condition pour créer un nouveau utilisateur
        if ($fk_tiers == "" && $fk_tier == "" && $fk_tiers_phone == "" && $fk_tiers_CU == "") {

            
            $date = date('Y-m-d');
            $dat = explode('-', $date);
            $a1 = $dat[0];
            // recupérer les deux derniers chiffres;
            $a11 = substr($a1, -2);
            $a2 = $dat[1];
            
            $socid = "news";
            $woo = $compagny;
            $type_id = "";
            $typent_code = "";

            // defini si le client est un professionnel.
            if ($woo != "") {
                $type_id = "235";
                $typent_code = "PROF";
            }

            if (isset($is_professionel)) {
                if ($is_professionel == true) {
                    $type_id = "235";
                    $typent_code = "PROF";
                }
            } else {
                $type_id = "";
                $typent_code = "";
            }

            $name = "";

            $chaine_index = "GAL";
            if (strpos($order_id, $chaine_index) !== false) {
                $code_client = $order_id;
            } else {
                $code = $id_cusotmer;
                $code_client = "WC-$a2$a11-$code"; // créer le code client du tiers...
            }

            // recupérer le prefix pays à partir du code client 
            $code_country = $code_country;
            $id_country = array_search($code_country, $data_ids_country);
            if ($id_country == "") {
                $id_country = 1;
                $code_country = "FR";
            }
            if ($id_country != "") {
                $id_country = array_search($code_country, $data_ids_country);
                $code_country = $id_country;
            }

            // create tiers ajout d'une array options pour id_woocomerce
            $tiers_options = [
                "options_id_wc" => $id_cusotmer
            ];

            $data_tiers[] = [
                'entity' => '1',
                'name' => $first_name . ' ' . $last_name,
                'name_alias' => $woo,
                'address' => $adresse,
                'zip' => $zip,
                'status' => '1',
                'email' => $email,
                "typent_id" => $type_id,
                "typent_code" => $typent_code,
                'phone' => $phone,
                'town' => $city,
                'client' => '1',
                'code_client' => $code_client,
                'country_id' => $id_country,
                'country_code' => $code_country,
                'array_options' => $tiers_options,
            ];

            $data_infos_user = [
                'first_name' => $first_name . ' ' . $last_name,
                'last_name' => '',
                'email' => $email,
            ];
            // recupérer un array pour créer un client via bdd base de données.
        }


        // cree le client pour la seconde condition(les client CU)
        // basons sur si l'email a changer et le phone a changer et id n'est pas data_code.


        // recupérer le socid.
        $this->setSocidtiers($socid);
        // renvoi un tableau
        return $data_tiers;
    }

    
}
