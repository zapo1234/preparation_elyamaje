<?php

namespace App\Repository\Categorie;

interface CategoriesInterface
{
  public function insertCategoriesOrUpdate($categroies);

  public function getAllCategories();

  public function updateCategoryOrder($id, $order_display, $parent);
}