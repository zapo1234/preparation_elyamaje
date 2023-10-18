<?php

namespace App\Repository\Label;

use App\Model\LabelMissing;

interface LabelMissingInterface
{
   public function getAllLabelsMissingStatusValid();

   public function insert($status, $order_id);

   public function delete($order_id);
}




