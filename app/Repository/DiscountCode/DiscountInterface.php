<?php

namespace App\Repository\DiscountCode;


interface DiscountInterface
{
    public function getDiscountCodes($startDate = null, $endDate = null, $code = null, $status = null, $limit = null, $status_updated = null);

    public function updateOrder($order_id, $data);
}




