<?php

namespace App\Repository\Reassort;

interface ReassortInterface
{
    public function getReassortByUser($user_id);

    public function findByIdentifiantReassort($identifiant, $cle = null);
    
    public function deleteByIdentifiantReassort($identifiant);

    public function update_in_hist_reassort($identifiant, $colonnes_values);

    public function checkProductBarcode($product_id, $barcode);

    public function checkIfDone($order_id, $barcode_array, $products_quantity);

    public function updateStatusReassort($transfer_id, $status);

    public function getAllCategoriesAndProducts();
    public function getAllCategoriesLabel();
    public function getKits();
    
}




