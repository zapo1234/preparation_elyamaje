<?php

namespace App\Repository\Product;


interface ProductInterface
{
    public function getAllProducts();

    public function getAllProductsPublished();

    public function insertProductsOrUpdate($products);
}




