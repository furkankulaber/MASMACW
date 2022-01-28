<?php
/*
 * @author		furkankulaber
 *
 * @copyright   Raviosoft (https://www.raviosoft.com) (C) 2021
 *
 *  @date        10.03.2021 23:20
 */

namespace App\Service\ManagerService;

use App\Entity\UserSession;
use App\Entity\User;
use App\Repository\UserSessionRepository;
use App\Security\Authenticated;
use App\Security\SecurityToken;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class TokenManager
{
    /** @var TokenStorageInterface */
    private TokenStorageInterface $tokenStorage;

    /** @var int|mixed */
    private int $tokenLifetime;

    /** @var ContainerInterface */
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->tokenStorage = $container->get('security.token_storage');
        $authParams = $container->getParameter('AUTH_PARAMS');
        $this->tokenLifetime = $authParams['TOKEN_LIFETIME'];
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function createToken(User $user)
    {
        /** @var UserSessionRepository $sessionRepo */
        $sessionRepo = $this->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository(UserSession::class);

        /**
         * Search if there is a valid session for user..
         * @var null|UserSession $availableSession
         */
        $availableSession = $sessionRepo->getAvailableSessionViaUserId($user->getId(), $user->getLastUserOfDevices());
        if ($availableSession->isSuccess() && $availableSession->getResponse() instanceof UserSession) {
            $this->createSecurityToken($availableSession->getResponse());
            return $availableSession->getResponse();
        }

        $expiredAt = (new \DateTime('now'))->add(new \DateInterval(sprintf("PT%sS", $this->tokenLifetime)));

        // Create a token for login..
        $tokenResponse = $sessionRepo->insert(
            [
                'user' => $user,
                'token' => $this->generateToken(),
                'device' => $user->getLastUserOfDevices()->first(),
                'expireAt' => $expiredAt
            ]
        );

        // If token creation has success..
        if ($tokenResponse->isSuccess() && $tokenResponse->getResponse() instanceof UserSession) {
            $this->createSecurityToken($tokenResponse->getResponse());
            return $tokenResponse->getResponse();
        }

        return false;
    }

    private function createSecurityToken(UserSession $session): ?TokenManager
    {
        $authenticatedUser = new Authenticated($session);
        $securityToken = new SecurityToken($authenticatedUser->getRoles());
        $securityToken->setUser($authenticatedUser);
        $securityToken->setAttribute('token', $session->getToken());
        $this->tokenStorage->setToken($securityToken);

        return $this;
    }

    public function getRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    public function generateToken()
    {
        $salt = $this->getRandomString() . "|" . (new \DateTime('now'))->getTimestamp();

        return hash('sha256', $salt);
    }
}
