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

   public function deleteLabelByTrackingNumber($tracking_number);

   public function updateLabelBordereau($parcel_number);

   public function getAllLabels();

   public function getAllLabelsByStatusAndDate($rangeDate);

   public function updateLabelStatus($labels);
}




