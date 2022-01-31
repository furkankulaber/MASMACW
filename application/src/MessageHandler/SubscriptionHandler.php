<?php

namespace App\MessageHandler;

use App\Message\Subscription;
use App\Service\PurchaseService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class SubscriptionHandler implements MessageHandlerInterface
{

    private PurchaseService $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    public function __invoke(Subscription $message)
    {
        $this->purchaseService->checkAndUpdatePurchase($message->getPurchase()->getId());
    }
}
