<?php

namespace App\Repository\Reassort;

interface ReassortInterface
{
    public function getReassortByUser($user_id);

    public function checkProductBarcode($product_id, $barcode);

    public function checkIfDone($order_id, $barcode_array, $products_quantity);

    public function updateStatusReassort($transfer_id, $status);
}




