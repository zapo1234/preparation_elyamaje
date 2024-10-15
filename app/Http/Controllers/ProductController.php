<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repository\Product\ProductRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Repository\Categorie\CategoriesRepository;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $productRepository;
    protected $productCategories;

    public function __construct(ProductRepository $productRepository, CategoriesRepository $productCategories)
    {
        $this->productRepository = $productRepository;
        $this->productCategories = $productCategories;
    }

    /**
     * Récupère les produits et les catégories de produits.
     *
     * Cette méthode obtient tous les produits du site woocommerce, les filtre par statut
     * (publié ou autre), et les formate pour renvoyer des informations utiles.
     * De plus, elle récupère toutes les catégories de produits disponibles
     * et les formate pour le retour.
     *
     * @param Request $request L'objet de requête HTTP contenant des paramètres
     *                         tels que le statut des produits.
     *
     * @return array Un tableau associatif contenant deux clés :
     *               - "products" : Un tableau de produits filtrés et formatés.
     *               - "categories" : Un tableau de catégories formatées.
     */
    public function getProductsCategories(Request $request)
    {
        $status = $request->get('status') ?? "publish";

        // Get all products
        $products = $this->productRepository->getAllProducts()->toArray();
        $formattedProducts = array_map(function($product) use ($status) {
            if($product['status'] == $status){
                return [
                    'id_product_wc' => $product['product_woocommerce_id'],
                    'name' => $product['name'],
                    'permalink' => $product['url'],
                    'image_url' => $product['image'],
                    'categories_id' => explode(',', $product['category_id']),
                    'categories_name' => explode(',', $product['category']),
                ];
            } 
            return null;
        }, $products);

        $formattedProducts = array_values(array_filter($formattedProducts));

        $categories = $this->productCategories->getAllCategoriesNotSorted();
        $formattedCategories = array_map(function($category) {
            return [
                'id_category_wc' => $category['category_id_woocommerce'],
                'name' => $category['name'],
                'parent' => $category['parent_category_id'],
            ];
        }, $categories);

        return [
            "products" => $formattedProducts,
            "categories" => $formattedCategories
        ];
    } 
}