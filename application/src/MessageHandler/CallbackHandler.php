<?php

namespace App\MessageHandler;

use App\Message\Callback;
use App\Service\PurchaseService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class CallbackHandler implements MessageHandlerInterface
{
    private PurchaseService $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    public function __invoke(Callback $message)
    {
        $response = $this->purchaseService->callbackRequest($message->getPurchase()->getId(),$message->getEvent());
    }

}
