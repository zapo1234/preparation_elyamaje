<?php

namespace App\Http\Service\Api;

use PDO;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AddlineInvoicePay
{
    private $api;
    
    public function __construct(Api $api)
    {
        // Constructeur
        $this->api = $api;
    }

    public function AddlinepayInvoice($inv, $montant1, $montant2, $montant3, $ref, $newCommandepaye, $newbank, $apiKey, $apiUrl)
    {
        // Fonction pour ajouter les lignes de paiement sur les factures (cb/espece/card cadeaux.)
        // ajouter des paiment espéce et carte cadeaux sur les facture
        
        // Appel API pour ajouter un paiement
        $response_num = $this->api->CallAPI("POST", $apiKey, $apiUrl . "invoices/" . $inv . "/payments", json_encode($newbank));
        
        // Appel API pour mettre à jour la facture
        $this->api->CallAPI("PUT", $apiKey, $apiUrl . "invoices/" . $inv, json_encode($newCommandepaye));
        
        // Faire un select sur la table paiement
        $data = DB::connection('mysql2')->select("SELECT rowid, ref, num_paiement, fk_bank FROM llxyq_paiement WHERE rowid = ?", [$response_num]);
        $name_list = json_decode(json_encode($data), true);
        
        // Préparer les variables pour les updates
        $ref_paiement = $name_list[0]['ref'];
        $index_row = explode('-', $ref_paiement);
        $index_pay = $index_row[1] + 1;
        $index_pay1 = $index_pay+1;
        $fk_banks = $name_list[0]['fk_bank'];
        $fk_bank = $name_list[0]['fk_bank'] + 1; // Le fk bank suivant(logique)
        $ref_definitive = $index_row[0] . '-' . $index_pay;
        $ref_definitive1 = $index_row[0] . '-' . $index_pay1;
        $rowid_auto = $name_list[0]['rowid'] + 1; // Ligne insérée suivante
        $fk_account = 50; // Espèce Gala    prod && transfertx
        $paimentid = 4; // Méthode paiement espèce tranfertx et prod
        //$fk_account2 = 51; // Carte cadeaux gala transfertx
        $fk_account2 = 53;// prod compte cado
        $paimentid2 = 57; // Carte cadeaux
        $num_paiement = $name_list[0]['num_paiement'];
        
        // Reconstruire le montant cb de la facture
        DB::connection('mysql2')
            ->table('llxyq_paiement_facture')
            ->where('fk_facture', '=', $inv)
            ->update(['amount' => $montant1]);
        
        // Modifier le montant dans la ligne de paiement
        DB::connection('mysql2')
            ->table('llxyq_paiement')
            ->where('rowid', '=', $response_num)
            ->update(['amount' => $montant1, 'multicurrency_amount' => $montant1]);
        
        // Modifier dans l'écriture de la banque avec le montant
        DB::connection('mysql2')
            ->table('llxyq_bank')
            ->where('rowid', '=', $fk_banks)
            ->update(['amount' => $montant1]);
        
        // Insérer le montant en espèces dans la banque
        DB::connection('mysql2')->table('llxyq_bank')->insert([
            'datec' => now(),
            'tms' => now(),
            'datev' => now(),
            'dateo' => now(),
            'amount' => $montant2,
            'label' => "Paiement en espèce Gala Marseille",
            'fk_account' => $fk_account,
            'fk_user_author' => 0,
            'fk_user_rappro' => 0,
            'fk_type' => 'LIQ',
            'num_releve' => '',
            'num_chq' => $ref,
            'numero_compte' => '',
            'rappro' => 0,
            'note' => '',
            'fk_bordereau' => 0,
            'banque' => '',
            'emetteur' => '',
            'author' => '',
            'origin_id' => 0,
            'origin_type' => '',
            'import_key' => '',
            'amount_main_currency' => 0.00000000
            // Ajoutez d'autres colonnes et valeurs selon votre besoin
        ]);

        // Insérer le paiement en espèces
        DB::connection('mysql2')->table('llxyq_paiement')->insert([
            'ref' => $ref_definitive,
            'ref_ext' => '',
            'entity' => 1,
            'datec' => now(),
            'tms' => now(),
            'datep' => now(),
            'amount' => $montant2,
            'multicurrency_amount' => $montant2,
            'fk_paiement' => $paimentid,
            'num_paiement' => $num_paiement,
            'note' => '',
            'ext_payment_id' => '',
            'ext_payment_site' => '',
            'fk_bank' => $fk_bank,
            'fk_user_creat' => 0,
            'fk_user_modif' => 0,
            'fk_export_compta' => 0,
            'statut' => 0,
            'pos_change' => 0.00000000
            // Ajoutez d'autres colonnes et valeurs selon votre besoin
        ]);

        // Insérer l'écriture de paiement facture du montant en espèces
        DB::connection('mysql2')->table('llxyq_paiement_facture')->insert([
            'fk_paiement' => $rowid_auto,
            'fk_facture' => $inv,
            'amount' => $montant2,
        ]);

        // Ajouter une ligne de carte cadeaux dans la banque concernée
        DB::connection('mysql2')->table('llxyq_bank')->insert([
            'datec' => now(),
            'tms' => now(),
            'datev' => now(),
            'dateo' => now(),
            'amount' => $montant3,
            'label' => "Utilisation de bon d'achat Gal-M",
            'fk_account' => $fk_account2,
            'fk_user_author' => 0,
            'fk_user_rappro' => 0,
            'fk_type' => 'CADO',
            'num_releve' => '',
            'num_chq' => $ref,
            'numero_compte' => '',
            'rappro' => 0,
            'note' => '',
            'fk_bordereau' => 0,
            'banque' => '',
            'emetteur' => '',
            'author' => '',
            'origin_id' => 0,
            'origin_type' => '',
            'import_key' => '',
            'amount_main_currency' => 0.00000000
            // Ajoutez d'autres colonnes et valeurs selon votre besoin
        ]);

        // Insérer le montant bon cadeaux
        DB::connection('mysql2')->table('llxyq_paiement')->insert([
            'ref' => $ref_definitive1,
            'ref_ext' => '',
            'entity' => 1,
            'datec' => now(),
            'tms' => now(),
            'datep' => now(),
            'amount' => $montant3,
            'multicurrency_amount' => $montant3,
            'fk_paiement' => $paimentid2,
            'num_paiement' => $num_paiement,
            'note' => '',
            'ext_payment_id' => '',
            'ext_payment_site' => '',
            'fk_bank' => $fk_bank + 1, // Ajouter la ligne suivante
            'fk_user_creat' => 0,
            'fk_user_modif' => 0,
            'fk_export_compta' => 0,
            'statut' => 0,
            'pos_change' => 0.00000000
            // Ajoutez d'autres colonnes et valeurs selon votre besoin
        ]);

        // Insérer l'écriture de paiement facture pour le bon d'achat du gala
        DB::connection('mysql2')->table('llxyq_paiement_facture')->insert([
            'fk_paiement' => $rowid_auto + 1, // Ajouter la ligne suivante(logique)
            'fk_facture' => $inv,
            'amount' => $montant3,
        ]);
    }

    public function AddlinepayInvoices($inv, $montant1, $montant2, $ref, $newCommandepaye, $newbank, $apiKey, $apiUrl)
    {
        // Ajouter un paiement espèce 
        $response_num = $this->api->CallAPI("POST", $apiKey, $apiUrl . "invoices/" . $inv . "/payments", json_encode($newbank));
        // Mettre à jour la facture
        $this->api->CallAPI("PUT", $apiKey, $apiUrl . "invoices/" . $inv, json_encode($newCommandepaye));
        // Faire un select sur la table paiement
        $data = DB::connection('mysql2')->select("SELECT rowid, ref, num_paiement, fk_bank FROM llxyq_paiement WHERE rowid = ?", [$response_num]);
        $name_list = json_decode(json_encode($data), true);
        
        // Préparer les variables pour les updates
        $ref_paiement = $name_list[0]['ref'];
        $index_row = explode('-', $ref_paiement);
        $index_pay = $index_row[1] + 1;
        $fk_banks = $name_list[0]['fk_bank'];
        $fk_bank = $name_list[0]['fk_bank'] + 1; // Le fk bank suivant
        $ref_definitive = $index_row[0] . '-' . $index_pay;
        $rowid_auto = $name_list[0]['rowid'] + 1; // Ligne insérée suivante
        $fk_account = 50; // Espèce Gala
        $paimentid = 4; // Méthode paiement espèce
        $num_paiement = $name_list[0]['num_paiement'];
        
        // Reconstruire le montant cb de la facture
        DB::connection('mysql2')
            ->table('llxyq_paiement_facture')
            ->where('fk_facture', '=', $inv)
            ->update(['amount' => $montant1]);
        
        // Modifier le montant dans la ligne de paiement
        DB::connection('mysql2')
            ->table('llxyq_paiement')
            ->where('rowid', '=', $response_num)
            ->update(['amount' => $montant1, 'multicurrency_amount' => $montant1]);
        
        // Modifier dans l'écriture de la banque avec le montant
        DB::connection('mysql2')
            ->table('llxyq_bank')
            ->where('rowid', '=', $fk_banks)
            ->update(['amount' => $montant1]);
        
        // Insérer le montant en espèces dans la banque
        DB::connection('mysql2')->table('llxyq_bank')->insert([
            'datec' => now(),
            'tms' => now(),
            'datev' => now(),
            'dateo' => now(),
            'amount' => $montant2,
            'label' => "Paiement en espèce Gala-M",
            'fk_account' => $fk_account,
            'fk_user_author' => 0,
            'fk_user_rappro' => 0,
            'fk_type' => 'LIQ',
            'num_releve' => '',
            'num_chq' => $ref,
            'numero_compte' => '',
            'rappro' => 0,
            'note' => '',
            'fk_bordereau' => 0,
            'banque' => '',
            'emetteur' => '',
            'author' => '',
            'origin_id' => 0,
            'origin_type' => '',
            'import_key' => '',
            'amount_main_currency' => 0.00000000
            // Ajoutez d'autres colonnes et valeurs selon votre besoin
        ]);

        // Insérer le paiement en espèces
        DB::connection('mysql2')->table('llxyq_paiement')->insert([
            'ref' => $ref_definitive,
            'ref_ext' => '',
            'entity' => 1,
            'datec' => now(),
            'tms' => now(),
            'datep' => now(),
            'amount' => $montant2,
            'multicurrency_amount' => $montant2,
            'fk_paiement' => $paimentid,
            'num_paiement' => $num_paiement,
            'note' => '',
            'ext_payment_id' => '',
            'ext_payment_site' => '',
            'fk_bank' => $fk_bank,
            'fk_user_creat' => 0,
            'fk_user_modif' => 0,
            'fk_export_compta' => 0,
            'statut' => 0,
            'pos_change' => 0.00000000
            // Ajoutez d'autres colonnes et valeurs selon votre besoin
        ]);

        // Insérer l'écriture de paiement facture du montant en espèces
        DB::connection('mysql2')->table('llxyq_paiement_facture')->insert([
            'fk_paiement' => $rowid_auto,
            'fk_facture' => $inv,
            'amount' => $montant2,
        ]);
    }


    public function Addlinepaykdo($inv, $montant1, $montant2, $ref,$newCommandepaye, $newbank,$apiKey, $apiUrl){
    
        // Ajouter un paiement carte cado 
        $response_num = $this->api->CallAPI("POST", $apiKey, $apiUrl . "invoices/" . $inv . "/payments", json_encode($newbank));
        // Mettre à jour la facture
        $this->api->CallAPI("PUT", $apiKey, $apiUrl . "invoices/" . $inv, json_encode($newCommandepaye));
        // Faire un select sur la table paiement
        $data = DB::connection('mysql2')->select("SELECT rowid, ref, num_paiement, fk_bank FROM llxyq_paiement WHERE rowid = ?", [$response_num]);
        $name_list = json_decode(json_encode($data), true);
        
        // Préparer les variables pour les updates
        $ref_paiement = $name_list[0]['ref'];
        $index_row = explode('-', $ref_paiement);
        $index_pay = $index_row[1] + 1;
        $fk_banks = $name_list[0]['fk_bank'];
        $fk_bank = $name_list[0]['fk_bank'] + 1; // Le fk bank suivant
        $ref_definitive = $index_row[0] . '-' . $index_pay;
        $rowid_auto = $name_list[0]['rowid'] + 1; // Ligne insérée suivante
        //$fk_account = 51;// card cadeaux transfertx
        $fk_account = 53; // prod
        $paimentid =57;// carte cadeaux
        $num_paiement = $name_list[0]['num_paiement'];
        
        // Reconstruire le montant cb de la facture
        DB::connection('mysql2')
            ->table('llxyq_paiement_facture')
            ->where('fk_facture', '=', $inv)
            ->update(['amount' => $montant1]);
        
        // Modifier le montant dans la ligne de paiement
        DB::connection('mysql2')
            ->table('llxyq_paiement')
            ->where('rowid', '=', $response_num)
            ->update(['amount' => $montant1, 'multicurrency_amount' => $montant1]);
        
        // Modifier dans l'écriture de la banque avec le montant
        DB::connection('mysql2')
            ->table('llxyq_bank')
            ->where('rowid', '=', $fk_banks)
            ->update(['amount' => $montant1]);
        
        // Insérer le montant en espèces dans la banque
        DB::connection('mysql2')->table('llxyq_bank')->insert([
            'datec' => now(),
            'tms' => now(),
            'datev' => now(),
            'dateo' => now(),
            'amount' => $montant2,
            'label' => "utilisation de bon d'achat Gal-M",
            'fk_account' => $fk_account,
            'fk_user_author' => 0,
            'fk_user_rappro' => 0,
            'fk_type' => 'CADO',
            'num_releve' => '',
            'num_chq' => $ref,
            'numero_compte' => '',
            'rappro' => 0,
            'note' => '',
            'fk_bordereau' => 0,
            'banque' => '',
            'emetteur' => '',
            'author' => '',
            'origin_id' => 0,
            'origin_type' => '',
            'import_key' => '',
            'amount_main_currency' => 0.00000000
            // Ajoutez d'autres colonnes et valeurs selon votre besoin
        ]);

        // Insérer le paiement en espèces
        DB::connection('mysql2')->table('llxyq_paiement')->insert([
            'ref' => $ref_definitive,
            'ref_ext' => '',
            'entity' => 1,
            'datec' => now(),
            'tms' => now(),
            'datep' => now(),
            'amount' => $montant2,
            'multicurrency_amount' => $montant2,
            'fk_paiement' => $paimentid,
            'num_paiement' => $num_paiement,
            'note' => '',
            'ext_payment_id' => '',
            'ext_payment_site' => '',
            'fk_bank' => $fk_bank,
            'fk_user_creat' => 0,
            'fk_user_modif' => 0,
            'fk_export_compta' => 0,
            'statut' => 0,
            'pos_change' => 0.00000000
            // Ajoutez d'autres colonnes et valeurs selon votre besoin
        ]);

        // Insérer l'écriture de paiement facture du montant en espèces
        DB::connection('mysql2')->table('llxyq_paiement_facture')->insert([
            'fk_paiement' => $rowid_auto,
            'fk_facture' => $inv,
            'amount' => $montant2,
        ]);


    }

    public function reconstruirecdo($inv,$ref,$newCommandepaye, $newbank,$apiKey, $apiUrl){
    // dans le cas de kdo 100% reconstruire le montant.
    $response_num = $this->api->CallAPI("POST", $apiKey, $apiUrl . "invoices/" . $inv . "/payments", json_encode($newbank));
    // Mettre à jour la facture
    $this->api->CallAPI("PUT", $apiKey, $apiUrl . "invoices/" . $inv, json_encode($newCommandepaye));
    // Faire un select sur la table paiement
     $data = DB::connection('mysql2')->select("SELECT rowid, ref, num_paiement, fk_bank,amount FROM llxyq_paiement WHERE rowid = ?", [$response_num]);
    $name_list = json_decode(json_encode($data), true);

      // Préparer les variables pour les updates
      $ref_paiement = $name_list[0]['ref'];
      $index_row = explode('-', $ref_paiement);
      $index_pay = $index_row[1] + 1;
      $fk_banks = $name_list[0]['fk_bank'];
      $fk_bank = $name_list[0]['fk_bank'] + 1; // Le fk bank suivant
      $ref_definitive = $index_row[0] . '-' . $index_pay;
      $rowid_auto = $name_list[0]['rowid'] + 1; // Ligne insérée suivante
      $fk_account = 50; // Espèce Gala
      $paimentid = 4; // Méthode paiement espèce
      $num_paiement = $name_list[0]['num_paiement'];

      $amount = -$name_list[0]['amount'];// fournir un nombre negatif pour kdo
      
      // Reconstruire le montant cb de la facture
      DB::connection('mysql2')
          ->table('llxyq_paiement_facture')
          ->where('fk_facture', '=', $inv)
          ->update(['amount' => $amount]);
      
      // Modifier le montant dans la ligne de paiement
      DB::connection('mysql2')
          ->table('llxyq_paiement')
          ->where('rowid', '=', $response_num)
          ->update(['amount' => $amount, 'multicurrency_amount' => $amount]);
      
      // Modifier dans l'écriture de la banque avec le montant
      DB::connection('mysql2')
          ->table('llxyq_bank')
          ->where('rowid', '=', $fk_banks)
          ->update(['amount' => $amount]);

    }
}
