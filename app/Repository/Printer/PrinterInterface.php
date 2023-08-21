<?php

namespace App\Repository\Printer;


interface PrinterInterface
{
   public function getPrinters();

   public function addPrinter($data);

   public function updatePrinter($data, $printer_id);

   public function deletePrinter($printer_id);

   public function getPrinterByUser($user_id);
}




