<?php

namespace App\Message;

use App\Entity\Purchase;

final class Subscription
{

    private Purchase $purchase;

    /**
     * @param Purchase $purchase
     */
    public function __construct(Purchase $purchase)
    {
        $this->purchase = $purchase;
    }


    /**
     * @return Purchase
     */
    public function getPurchase(): Purchase
    {
        return $this->purchase;
    }
}
