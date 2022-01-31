<?php

namespace App\Service;

use App\Entity\Platform;
use App\Entity\Purchase;
use App\Entity\UserDevice;
use App\Message\Callback;
use App\Message\Subscription;
use App\Repository\PurchaseRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class PurchaseService
{

    /** @var ContainerInterface */
    protected ContainerInterface $container;
    protected HttpClientInterface $client;
    private $config;
    protected MessageBusInterface $messageBus;

    const TIMEZONE = 'Europe/Istanbul';

    /**
     * @param ContainerInterface $container
     * @param HttpClientInterface $client
     */
    public function __construct(ContainerInterface $container, HttpClientInterface $client, MessageBusInterface $bus)
    {
        $this->container = $container;
        $this->client = $client;
        $this->messageBus = $bus;
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

    /**
     * @return MessageBusInterface
     */
    public function getMessageBus(): MessageBusInterface
    {
        return $this->messageBus;
    }

    public function checkPurchase(UserDevice $userDevice)
    {
        $purchaseRepo = ($this->getContainer()->get('doctrine')->getManager())->getRepository(Purchase::class);
        $receiptCheckResponse = $purchaseRepo->findOneBy(['device' => $userDevice, 'status' => 'a']);
        if ($receiptCheckResponse->getResponse() instanceof Purchase) {
            $nowDate = new \DateTime('now', new \DateTimeZone(self::TIMEZONE));
            $expDate = $receiptCheckResponse->getResponse()->getExpireAt();
            if ($nowDate->getTimestamp() > $expDate->getTimeStamp()) {
                $receiptCheckResponse = $purchaseRepo->update($receiptCheckResponse->getResponse(), [
                    'status' => 'w'
                ]);
                $this->getMessageBus()->dispatch(new Subscription($receiptCheckResponse->getResponse()));
            }
            return new ServiceResponse($receiptCheckResponse->getResponse());
        }
        return new ServiceResponse(false);
    }

    public function purchaseEvent($receipt, UserDevice $userDevice)
    {
        $purchaseRepo = ($this->getContainer()->get('doctrine')->getManager())->getRepository(Purchase::class);
        $receiptCheckResponse = $purchaseRepo->findOneBy(['device' => $userDevice, 'status' => 'a']);
        if ($receiptCheckResponse->getResponse() instanceof Purchase) {
            return new ServiceResponse($receiptCheckResponse->getResponse());
        }

        $platform = $userDevice->getPlatform();
        $receiptResponse = $this->requestPlatform($receipt, $platform);
        if ($receiptResponse->getException()) {
            return new ServiceResponse($receiptResponse->getException());
        }
        $receiptResponseData = $receiptResponse->getResponse();
        if (isset($receiptResponseData['status']) && $receiptResponseData['status'] === true) {
            /** @var PurchaseRepository $purchaseRepo */
            $expDate = new \DateTime($receiptResponseData['expireAt']);
            $expDate->setTimezone(new \DateTimeZone(self::TIMEZONE));
            $insertResponse = $purchaseRepo->insert([
                'expireAt' => $expDate,
                'user' => $userDevice->getUser(),
                'platform' => $userDevice->getPlatform(),
                'device' => $userDevice,
                'receipt' => $receipt,
                'status' => 'a'
            ]);
            if ($insertResponse->getException() || !$insertResponse->getResponse() instanceof Purchase) {
                return new ServiceResponse($insertResponse->getException());
            }
            $this->getMessageBus()->dispatch(new Callback($insertResponse->getResponse(),'started'));
            return new ServiceResponse($insertResponse->getResponse());
        }
        return new ServiceResponse(false);

    }


    protected function requestPlatform($receipt, Platform $platform, $url = '')
    {
        $platformSettings = $platform->getSettings();
        $authUsername = $platformSettings['username'];
        $authPassword = $platformSettings['password'];

        try {
            $response = $this->client->request(
                'POST',
                $platformSettings['url'] . $url,
                [
                    'auth_basic' => [$authUsername, $authPassword],
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

    public function checkAndUpdatePurchase($purchaseId)
    {

        $purchaseRepo = ($this->getContainer()->get('doctrine')->getManager())->getRepository(Purchase::class);
        /** @var Purchase $purchase */
        $purchaseResponse = $purchaseRepo->findOneBy(['id' => $purchaseId]);
        $purchase = $purchaseResponse->getResponse();
        if ($purchase->getStatus() === 'd') {
            $diff = abs(strtotime("2022-01-31 13:30:14") - time()) / 60;
            if ($diff < 10) {
                return true;
            }
        }
        $receiptResponse = $this->requestPlatform($purchase->getReceipt(), $purchase->getPlatform(), '/check');
        if ($receiptResponse->getException()) {
            $this->getMessageBus()->dispatch(new Subscription($purchase), [
                new DelayStamp(60000)
            ]);
            return true;
        }
        $receiptResponseData = $receiptResponse->getResponse();
        if (isset($receiptResponseData['status']) && $receiptResponseData['status'] === true) {
            /** @var PurchaseRepository $purchaseRepo */
            $expDate = new \DateTime($receiptResponseData['expireAt']);
            $expDate->setTimezone(new \DateTimeZone(self::TIMEZONE));
            $updateResponse = $purchaseRepo->update($purchase, [
                'expireAt' => $expDate,
                'status' => 'a'
            ]);
            $this->getMessageBus()->dispatch(new Callback($updateResponse->getResponse(),'renew'));
            if ($updateResponse->getException() || !$updateResponse->getResponse() instanceof Purchase) {
                $this->getMessageBus()->dispatch(new Subscription($purchase), [
                    new DelayStamp(60000)
                ]);
                return true;
            }
            return true;
        } else if (isset($receiptResponseData['status']) && $receiptResponseData['status'] === 'wait') {
            $updateResponse = $purchaseRepo->update($purchase, [
                'status' => 'd'
            ]);
        } else if (isset($receiptResponseData['status']) && $receiptResponseData['status'] === false) {
            $updateResponse = $purchaseRepo->update($purchase, [
                'status' => 'e'
            ]);
            $this->getMessageBus()->dispatch(new Callback($updateResponse->getResponse(),'canceled'));
            return true;
        }
        return true;
    }

    public function callbackRequest($purchaseId,$event)
    {
        $purchaseRepo = ($this->getContainer()->get('doctrine')->getManager())->getRepository(Purchase::class);
        /** @var Purchase $purchase */
        $purchaseResponse = $purchaseRepo->findOneBy(['id' => $purchaseId]);
        $purchase = $purchaseResponse->getResponse();

        $platformSettings = $purchase->getPlatform()->getSettings();
        $authUsername = $platformSettings['username'];
        $authPassword = $platformSettings['password'];

        try {
            $response = $this->client->request(
                'POST',
                $platformSettings['callback'],
                [
                    'auth_basic' => [$authUsername, $authPassword],
                    'json' => [
                        'appID' => $purchase->getPlatform()->getApp(),
                        'deviceId' => $purchase->getDevice(),
                        'event' => $event
                    ],
                ]
            );
            return new ServiceResponse(json_decode($response->getContent(), true));
        } catch (\Exception $exception) {
            return new ServiceResponse($exception);
        }
    }


}
