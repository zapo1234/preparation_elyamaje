<?php

namespace App\Repository\Label;

use App\Model\Labels;

interface LabelInterface
{
   public function save($label);

   public function getLabels();

   public function getLabelById($label);
}




