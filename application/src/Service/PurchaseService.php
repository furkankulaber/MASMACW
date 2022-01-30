<?php

namespace App\Service;

use App\Entity\Platform;
use App\Entity\Purchase;
use App\Entity\UserDevice;
use App\Repository\PurchaseRepository;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class PurchaseService
{

    /** @var ContainerInterface */
    protected ContainerInterface $container;
    protected HttpClientInterface $client;
    private $config;

    /**
     * @param ContainerInterface $container
     * @param HttpClientInterface $client
     */
    public function __construct(ContainerInterface $container, HttpClientInterface $client)
    {
        $this->container = $container;
        $this->client = $client;
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return HttpClientInterface
     */
    protected function getClient(): HttpClientInterface
    {
        return $this->client;
    }

    public function purchaseEvent($receipt, UserDevice $userDevice)
    {
        $purchaseRepo = ($this->getContainer()->get('doctrine')->getManager())->getRepository(Purchase::class);
        $receiptCheckResponse = $purchaseRepo->findOneBy(['receipt' => $receipt, 'device' => $userDevice]);
        if($receiptCheckResponse->getResponse() instanceof Purchase)
        {
            return new ServiceResponse($receiptCheckResponse->getResponse());
        }

        $platform = $userDevice->getPlatform();
        $receiptResponse = $this->requestPlatform($receipt, $platform);
        if($receiptResponse->getException())
        {
            return new ServiceResponse($receiptResponse->getException());
        }
        $receiptResponseData = $receiptResponse->getResponse();
        if(isset($receiptResponseData['status']) && $receiptResponseData['status'] === true)
        {
            /** @var PurchaseRepository $purchaseRepo */
            $expDate = new \DateTime($receiptResponseData['expireAt']);
            $expDate->setTimezone(new \DateTimeZone('Europe/Istanbul'));
            $insertResponse = $purchaseRepo->insert([
                'expireAt' => $expDate,
                'user' => $userDevice->getUser(),
                'platform' => $userDevice->getPlatform(),
                'device' => $userDevice,
                'receipt' => $receipt,
                'status' => 's'
            ]);
            if($insertResponse->getException() || !$insertResponse->getResponse() instanceof Purchase)
            {
                return new ServiceResponse($receiptResponse->getException());
            }
            return new ServiceResponse($insertResponse->getResponse());
        }
        return new ServiceResponse(false);

    }


    protected function requestPlatform($receipt, Platform $platform)
    {

        $platformSettings = $platform->getSettings();
        $authUsername = $platformSettings['username'];
        $authPassword = $platformSettings['password'];

        try {
            $response = $this->client->request(
                'POST',
                $platformSettings['url'],
                [
                    'auth_basic' => [$authUsername,$authPassword],
                    'json' => [
                        'receipt' => $receipt
                    ],
                ]
            );
            return new ServiceResponse(json_decode($response->getContent(), true));
        } catch (\Exception $exception) {
            return new ServiceResponse($exception);
        }
    }


}