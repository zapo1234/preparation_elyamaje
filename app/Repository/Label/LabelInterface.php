<?php

namespace App\Repository\Label;

use App\Model\Labels;

interface LabelInterface
{
   public function save($label);

   public function getLabels();

   public function getLabelById($label);

   public function getParcelNumbersyDate($date);

   public function saveBordereau($bordereau_id, $parcelNumbers_array);

   public function deleteLabelById($label_id);

}




