<?php

namespace App\Repository\Product;


interface ProductInterface
{
    public function getAllProducts();

    public function insertProductsOrUpdate($products);
}




