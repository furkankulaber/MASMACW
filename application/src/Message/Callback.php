<?php

namespace App\Message;

use App\Entity\Purchase;

final class Callback
{
    private Purchase $purchase;
    private string $event;

    /**
     * @param Purchase $purchase
     */
    public function __construct(Purchase $purchase, $event)
    {
        $this->purchase = $purchase;
        $this->event = $event;
    }


    /**
     * @return Purchase
     */
    public function getPurchase(): Purchase
    {
        return $this->purchase;
    }

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }
}
