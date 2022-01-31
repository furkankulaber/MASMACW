<?php

namespace App\MessageHandler;

use App\Message\Callback;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class CallbackHandler implements MessageHandlerInterface
{


    public function __construct()
    {

    }

    public function __invoke(Callback $message)
    {
    }

}
