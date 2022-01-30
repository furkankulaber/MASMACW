<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\AuthenticatedApp;
use App\Service\ManagerService\AuthenticationManager;
use App\Service\ManagerService\TokenManager;
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

        $data = json_decode($this->getRequest()->getContent(), true);
        $userService = new UserService($this->getContainer());
        $userResponse = $userService->createUserWithDevice($data, $this->getXDeviceId(), $this->getPlatform());
        if (!$userResponse->getResponse() instanceof User) {
            return $this->getResponseService()->toJsonResponse(false);
        }
        $user = $userResponse->getResponse();
        $session = $userService->createToken($user, $tokenManager);

        return $this->getResponseService()->withSessionToken($session->getToken())->toJsonResponse("OK");
    }

}
