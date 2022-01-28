<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\ManagerService\AuthenticationManager;
use App\Service\ManagerService\TokenManager;
use App\Service\UserService;
use App\Service\ResponseService\Constants;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends CoreController
{
    /**
     * @Route("/index", name="index")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/IndexController.php',
        ]);
    }

    /**
     * @Route("", name="_register", methods={"POST"})
     * @param AuthenticationManager $authenticationManager
     * @param TokenManager $tokenManager
     * @return JsonResponse
     */
    public function registerUser(
        AuthenticationManager $authenticationManager,
        TokenManager $tokenManager
    ): JsonResponse {
        /** @todo register kısımlarında uniq alanlar ile ilgili kontroller * */

        $pService = new ($this->container);
        $loggedIn = $authenticationManager->loginDeviceId(
            $this->getXDeviceId(),
            $this->getPlatform()
        );

        if (!$loggedIn) {
            $loggedInResponse = $pService->createUserWithDevice($this->getData(), $this->getXDeviceId(),
                $this->getPlatform());

            if (!$loggedInResponse->isSuccess() && $loggedInResponse->getResponse() instanceof User) {
                return $this->getResponseService()->toJsonResponse(
                    null, Constants::MSG_412_7000, $loggedInResponse->getMessage()
                );
            }
            $loggedIn = $loggedInResponse->getResponse();
        }

        $loggedIn = $pService->checkUserInfo($loggedIn);
        $session = $tokenManager->createToken($loggedIn);

        if (!$session) {
            return $this->getResponseService()->toJsonResponse(
                null, Constants::MSG_401_0002
            );
        }

        $response = $pService->userResponseInitialize($loggedIn);

        return $this->getResponseService()->withSessionToken($session->getToken())->toJsonResponse(
            ($response->getResponse()), Constants::MSG_200_0000
        );
    }
}
