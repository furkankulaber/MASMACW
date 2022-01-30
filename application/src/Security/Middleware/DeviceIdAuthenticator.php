<?php

namespace App\Security\Middleware;

use App\Entity\UserSession;
use App\Repository\UserSessionRepository;
use App\Security\Authenticated;
use App\Service\ManagerService\TokenManager;
use App\Service\ResponseService\Constants;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeviceIdAuthenticator extends AbstractGuardAuthenticator
{

    public const KEY_IDENTIFIER = 'X-Session-Token';

    /** @var Request  */
    private Request $request;

    /** @var TranslatorInterface  */
    private TranslatorInterface $translator;

    /** @var ContainerInterface  */
    private ContainerInterface $container;

    private TokenManager $tokenManager;

    public function __construct(TranslatorInterface $translator, ContainerInterface $container, TokenManager $tokenManager)
    {
        $this->translator = $translator;
        $this->container = $container;
        $this->tokenManager = $tokenManager;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        if (null !== $authException)
            throw new \Exception($this->translator->trans(Constants::MSG_401_0000), 401);

        return null;
    }

    public function supports(Request $request)
    {
        if($request->get("_route") === 'register' || $request->getPathInfo() === '/mock/google' || $request->getPathInfo() === '/mock/apple'){
            return false;
        }
        return true;
    }

    public function getCredentials(Request $request)
    {
        $this->request = $request;

        return array(
            'token' => $request->headers->get(self::KEY_IDENTIFIER)
        );
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (!isset($credentials['token']) || empty($credentials['token']))
            throw new \Exception($this->translator->trans(Constants::MSG_401_0000), 401);

        date_default_timezone_set("Europe/Istanbul");


        /** @var UserSessionRepository $sessionRepo */
        $sessionRepo = $this->container->get('doctrine.orm.default_entity_manager')->getRepository(UserSession::class);

        /** @var null|UserSession $availableSession */
        $availableSession = $sessionRepo->getAvailableSession($credentials['token']);

        if(null === $availableSession->getResponse())
            throw new \Exception($this->translator->trans(Constants::MSG_401_0000), 401);

        return new Authenticated($availableSession->getResponse());
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        throw $exception;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    public function supportsRememberMe()
    {
        return false;
    }

}
