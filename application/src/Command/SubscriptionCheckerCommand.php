<?php

namespace App\Command;

use App\Message\Subscription;
use App\Repository\PurchaseRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;


class SubscriptionCheckerCommand extends Command
{

    protected static $defaultName = 'app:subscription:checker';

    protected static $defaultDescription = 'Check Subscription';

    private ContainerInterface $container;
    private PurchaseRepository $purchaseRepository;
    private MessageBusInterface $messageBus;

    /**
     * @param ContainerInterface $container
     * @param PurchaseRepository $purchaseRepository
     */
    public function __construct(ContainerInterface $container, PurchaseRepository $purchaseRepository, MessageBusInterface $messageBus)
    {
        parent::__construct();
        $this->container = $container;
        $this->purchaseRepository = $purchaseRepository;
        $this->messageBus = $messageBus;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $response = $this->purchaseRepository->getWaitingOrExp();
        foreach ($response->getResponse() as $purchase)
        {
            $this->purchaseRepository->update($purchase,[
                'status' => 'q'
            ]);
            $this->messageBus->dispatch(new Subscription($purchase));
        }

        return Command::SUCCESS;
    }
}
