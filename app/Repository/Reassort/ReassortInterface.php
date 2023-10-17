<?php

namespace App\Repository\Reassort;

interface ReassortInterface
{
    public function getReassortByUser($user_id);

    public function getReassortById($id);

    public function findByIdentifiantReassort($identifiant, $cle = null);
    
    public function deleteByIdentifiantReassort($identifiant);

    public function update_in_hist_reassort($identifiant, $colonnes_values);

    public function checkProductBarcode($product_id, $barcode);

    public function checkIfDone($order_id, $barcode_array, $products_quantity);

    public function updateStatusReassort($transfer_id, $status);

    public function getAllCategoriesAndProducts($cat_lab);

    public function getAllCategoriesLabel();

    public function getKits();
    
    public function updateStatusTextReassort($transfer_id, $status);

    public function checkIfDoneTransfersDolibarr($order_id, $barcode_array, $products_quantity, $partial);

    public function getLastCategorie($cat, $catParent);
    public function updateUserReassort($id_user,$id_reassort);
    public function orderResetTransfers($order_id);
}




