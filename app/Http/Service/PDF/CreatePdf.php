<?php

namespace App\Http\Service\PDF;

use Dompdf\Dompdf;
use Dompdf\Options;

class CreatePdf
{

    private $dompdf; 

    public function __construct(Dompdf $dompdf, Options $options){
        $this->dompdf = $dompdf;
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
            $table.='<th style="border:1px solid black" bgcolor="white">Commandes Préparées</th>';
            $table.='<th style="border:1px solid black"bgcolor="white">Commandes Emballées</th>';
        $table .='</tr>';

        foreach ($data as $row) {
            $table .= '<tr>';
                $table .= '<td  style="border:1px solid black" bgcolor="white">' . $row['name'] . '</td>';
                $table .= '<td  style="border:1px solid black" bgcolor="white">' . $row['poste'] . '</td>';
                $table .= '<td  style="word-break:break-word;border:1px solid black" bgcolor="white">' . $row['prepared_order'] . '</td>';
                $table .= '<td  style="word-break:break-word;border:1px solid black" bgcolor="white">' . $row['finished_order'] . '</td>';
            $table .= '</tr>';
        }
        $table .= '</table>';
  
        $name = 'historique_order_'.$date_historique;
        $this->dompdf->loadHtml($table);
        $this->dompdf->render();
        return $this->dompdf->stream($name.'.pdf');
    }
      
  }


  









