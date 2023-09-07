<?php

namespace App\Repository\Product;


interface ProductInterface
{
    public function getAllProducts();

    public function getAllProductsPublished();

    public function insertProductsOrUpdate($products);

    public function updateProduct($id_product, $data);

    public function updateMultipleProduct($location, $products_id);

    public function getbarcodeproduct();

    public function checkProductBarcode($product_id, $barcode);
}




