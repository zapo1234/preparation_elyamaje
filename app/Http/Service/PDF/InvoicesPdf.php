<?php

namespace App\Http\Service;
use Exception;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;


class InvoicesPdf
{
    
      private $pdf; 
      
      public function __construct(PDF $pdf){
          $this->pdf = $pdf;
      }

       public function invoicespdf($data_line_order,$tiers,$ref_order,$total_ht,$total_ttc,$destinataire,$code_promo,$remise){

         //$ref_order="BP-marseille-000001";
         $date = date('Y-m-d H:i:s');
         $date = date('d/m/Y');
         
        // $destinataire ='zapomartial@yahoo.fr';
        $subject = 'Confirmation de commande Elyamaje lors de la Beauty Prof Paris 2024';
        $content = 'Bonjour Merci de recevoir votre fature de commande';
        
         $to = $destinataire;

         $view = View::make('invoices.tiersinvoice', [
          'date' => $date,
          'data_line_order' => $data_line_order,
          'tiers' => $tiers,
          'ref_order' => $ref_order,
          'total_ht' => $total_ht,
          'total_ttc' => $total_ttc,
          'code_promo'=>$code_promo,
          'remise'=>$remise,
      ]);
      
        try{

            $pdf =  $this->pdf->loadView('admin.tiersinvoice',['date'=>$date,'data_line_order'=>$data_line_order,'tiers'=>$tiers,'ref_order'=>$ref_order,'total_ht'=>$total_ht,'total_ttc'=>$total_ttc,'code_promo'=>$code_promo,'remise'=>$remise]);
            $pdfContent = $pdf->output();
            $filePath = 'invoices/'.$ref_order.'.pdf'; // Emplacement dans le dossier storage/app

            $filePaths = 'other_invoices/'.$ref_order.'.pdf';
            // Enregistrement du fichier PDF dans le répertoire de stockage
            Storage::put($filePath, $pdfContent);
            $path_invoices = "storage/app/$filePath";
            // recupérer ici les facture renvoye
            $path_invoice = "storage/app/$filePaths";

            // $to="adrien@elyamaje.com";
            // envoi de mail au client.
           /* Mail::send('email.invoice', ['ref_order'=>$ref_order,'code_promo'=>$code_promo], function ($message) use ($to, $subject, $content,$path_invoices) {
                      $message->to($to)
                      ->subject($subject)
                    ->attach($path_invoices);
                      
              });
            */
        } catch(Exception $e){

            DB::table('log_error')->insert([
                'order_id' => $ref_order,
                'message' => 'Application Caisse: '.$e->getMessage(),
            ]); 

            echo json_encode(['success' => false, 'message' => 'Oups ! Quelque chose s\'est mal passé']);
            return;
        }
      }
}

