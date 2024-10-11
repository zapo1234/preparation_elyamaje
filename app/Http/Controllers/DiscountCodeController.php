<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use App\Repository\DiscountCode\DiscountRepository;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DiscountCodeController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $discountRepository;

    public function __construct(DiscountRepository $discountRepository)
    {
        $this->discountRepository = $discountRepository;
    }


    /**
     * Récupère les codes de réduction filtrés en fonction des critères donnés.
     *
     * @param Request $request La requête HTTP contenant les paramètres de filtre.
     *
     * Paramètres acceptés dans la requête :
     * 
     * - `start_date` (string, optionnel) : Date de début du filtre (format 'Y-m-d').
     * - `end_date` (string, optionnel) : Date de fin du filtre (format 'Y-m-d').
     * - `code` (string, optionnel) : Code de réduction spécifique à filtrer.
     * - `status` (string, optionnel) : Statuts de la commande, pouvant être plusieurs séparés par une virgule (ex: 'processing,pending').
     * - `status_updated` (string, optionnel) : Date de mise à jour du statut (format 'Y-m-d').
     * - `limit` (int, optionnel) : Limite du nombre de résultats retournés.
     *
     * @return \Illuminate\Http\JsonResponse Liste des codes de réduction filtrés.
     */
    public function getFilteredDiscountCodes(Request $request)
    {
        // Extraire les filtres optionnels de la requête
        $startDate = $this->validateDate($request->get('start_date')) ? $request->get('start_date') : null;
        $endDate = $this->validateDate($request->get('end_date')) ? $request->get('end_date') : null;
        $code = $request->get('code') ?? null;
        $status = $request->get('status') ?? null;
        $status_updated =  $this->validateDate($request->get('status_updated')) ? $request->get('status_updated') : null;
        $limit = $request->get('limit') ?? null;

        // Appeler le repository pour récupérer les données
        return $this->discountRepository->getDiscountCodes($startDate, $endDate, $code, $status, $limit, $status_updated);
    }

    public function postOrderStatus(Request $request)
    {
        $order_id = $request->post('order_id');
        $status = $request->post('status');

        if($status && $order_id){
            $data = [
                "status" => $status,
                "status_updated" => date('Y-m-d H:i:s')
            ];
            return $this->discountRepository->updateOrder($order_id, $data);
        }
    }

    // Check date format
    private function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}