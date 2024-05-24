<?php

namespace App\Http\Service\PDF;

use Exception;
use Dompdf\Dompdf;
use Dompdf\Options;
use Barryvdh\DomPDF\PDF;

class CreatePdf
{

    private $dompdf; 
    private $loadPDF; 


    public function __construct(Dompdf $dompdf, Options $options, PDF $loadPDF){
        $this->dompdf = $dompdf;
        $this->loadPDF = $loadPDF;
        $options->set('defaultFont', 'Arial');
        $dompdf->setOptions($options);
    }

    public function generateHistoryOrders($data, $date_historique){

        $date_historique = date("d-m-Y", strtotime($date_historique));
        $table = '<h3 style="text-align:center">'.$date_historique.'</h3></br>';
        $table .= '<table style="width: 100%;" cellpadding="1" cellspacing="1">';
        $table .='<tr>';
            $table.='<th style="border:1px solid black" bgcolor="white">Nom</th>';
            $table.='<th style="border:1px solid black" bgcolor="white">Poste</th>';
            $table.='<th style="border:1px solid black" bgcolor="white">Préparées</th>';
            $table.='<th style="border:1px solid black" bgcolor="white">Total</th>';
            $table.='<th style="border:1px solid black"bgcolor="white">Produits</th>';
            $table.='<th style="border:1px solid black"bgcolor="white">Emballées</th>';
            $table.='<th style="border:1px solid black" bgcolor="white">Total</th>';
        $table .='</tr>';

        foreach ($data as $row) {
            $table .= '<tr>';
                $table .= '<td  style="border:1px solid black" bgcolor="white">' . $row['name'] . '</td>';
                $table .= '<td  style="border:1px solid black" bgcolor="white">' . implode(',',$row['poste']) ?? '' . '</td>';
                $table .= '<td  style="word-break:break-word;border:1px solid black" bgcolor="white">' . implode(',',$row['prepared_order']) . '</td>';
                $table .= '<td  style="word-break:break-word;border:1px solid black" bgcolor="white">' . $row['prepared_count'] . '</td>';
                $table .= '<td  style="word-break:break-word;border:1px solid black" bgcolor="white">' . $row['items_picked'] . '</td>';
                $table .= '<td  style="word-break:break-word;border:1px solid black" bgcolor="white">' . implode(',',$row['finished_order']) . '</td>';
                $table .= '<td  style="word-break:break-word;border:1px solid black" bgcolor="white">' . $row['finished_count']. '</td>';

            $table .= '</tr>';
        }
        $table .= '</table>';
  
        $name = 'historique_order_'.$date_historique;

        try{
            $this->dompdf->loadHtml($table);
            $this->dompdf->render();
             
            // Récupérer le contenu du PDF sous forme de chaîne
            $pdfContent =  $this->dompdf->output();
            
            return response()->make($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $name . '.pdf"'
            ]);
        } catch(Exception $e){
            dd($e->getMessage());
        }
    } 

    public function generateBordereauChrono($order_detail){
        $pdf = $this->loadPDF->loadView('admin.bordereauChrono', compact('order_detail'));
        return $pdf->stream('invoice.pdf');
    }
}


  









