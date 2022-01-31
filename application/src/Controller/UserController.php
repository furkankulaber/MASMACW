<?php

namespace App\Controller;

use App\Entity\Purchase;
use App\Entity\User;
use App\Exception\ApiCustomException;
use App\Security\AuthenticatedApp;
use App\Service\ManagerService\AuthenticationManager;
use App\Service\ManagerService\TokenManager;
use App\Service\PurchaseService;
use App\Service\ResponseService\Constants;
use App\Service\UserService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;

class UserController extends CoreController
{

    /**
     * @return JsonResponse
     * @Route("/register", name="register", methods={"POST"})
     * @IsGranted("ROLE_AUTHENTICATED_PLATFORM")
     * @throws ApiCustomException
     */
    public function register(AuthenticationManager $authenticationManager,
                             TokenManager          $tokenManager)
    {
        $this->checkValidate(['X-Device-Id' => $this->getXDeviceId()], new Assert\Collection([
            'X-Device-Id' => [
                new Assert\NotNull(),
                new Assert\NotBlank()
            ]
        ]));

        $userService = new UserService($this->getContainer());
        $userResponse = $userService->createUserWithDevice($this->getRequest(), $this->getXDeviceId(), $this->getPlatform());
        if (!$userResponse->getResponse() instanceof User) {
            return $this->getResponseService()->toJsonResponse($userResponse->getException(), Constants::MSG_500_0000);
        }
        $user = $userResponse->getResponse();
        $session = $userService->createToken($user, $tokenManager);

        return $this->getResponseService()->withSessionToken($session->getToken())->toJsonResponse("OK");
    }


    /**
     * @return JsonResponse
     * @Route("/purchase")
     * @IsGranted("ROLE_USER")
     */
    public function purchase(PurchaseService $purchaseService)
    {
        $data = json_decode($this->getRequest()->getContent(), true);
        if (isset($data['receipt'])) {
            $purchaseRespone = $purchaseService->purchaseEvent($data['receipt'], $this->getUser()->getLastUserOfDevices()->first());
            if ($purchaseRespone->getException()) {
                return $this->getResponseService()->toJsonResponse($purchaseRespone->getException());
            } else if (!$purchaseRespone->getResponse() instanceof Purchase) {
                return $this->getResponseService()->toJsonResponse(false);
            }

            /** @var Purchase $purchase */
            $purchase = $purchaseRespone->getResponse();
            return $this->getResponseService()->toJsonResponse(['receipt' => $purchase->getReceipt(), 'expireAt' => $purchase->getExpireAt()->format(DATE_ATOM), 'status' => $purchase->getStatus()]);
        }

        return $this->getResponseService()->toJsonResponse(false);
    }

    /**
     * @param PurchaseService $purchaseService
     * @return JsonResponse
     * @Route("/purchase/check")
     */
    public function checkPurchase(PurchaseService $purchaseService): JsonResponse
    {
        $purchaseRespone = $purchaseService->checkPurchase($this->getUser()->getLastUserOfDevices()->first());
        if (!$purchaseRespone->getResponse() instanceof Purchase) {
            return $this->getResponseService()->toJsonResponse(false);
        }
        /** @var Purchase $purchase */
        $purchase = $purchaseRespone->getResponse();
        return $this->getResponseService()->toJsonResponse(['receipt' => $purchase->getReceipt(), 'expireAt' => $purchase->getExpireAt()->format(DATE_ATOM), 'status' => $purchase->getStatus()]);
    }

}
