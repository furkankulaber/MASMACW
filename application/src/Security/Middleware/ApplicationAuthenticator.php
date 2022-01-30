<?php

namespace App\Security\Middleware;

use App\Entity\Platform;
use App\Entity\UserSession;
use App\Repository\PlatformRepository;
use App\Repository\RepositoryResponse;
use App\Repository\UserSessionRepository;
use App\Security\Authenticated;
use App\Security\AuthenticatedApp;
use App\Service\ResponseService\Constants;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApplicationAuthenticator extends AbstractGuardAuthenticator
{

    public const KEY_IDENTIFIER = 'X-Api-Key';

    /** @var Request  */
    private Request $request;

    /** @var TranslatorInterface  */
    private TranslatorInterface $translator;

    /** @var ContainerInterface  */
    private ContainerInterface $container;

    /** @var PlatformRepository */
    private PlatformRepository $platformRepository;

    public function __construct(TranslatorInterface $translator, ContainerInterface $container)
    {
        $this->translator = $translator;
        $this->container = $container;
        $this->platformRepository = $container->get('doctrine')->getRepository(Platform::class);
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        if (null !== $authException)
            throw new \Exception($this->translator->trans(Constants::MSG_401_0000), 401);

        return null;
    }

    public function supports(Request $request)
    {
        return null !== $request->headers->get(self::KEY_IDENTIFIER);
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

        /** @var RepositoryResponse $platform */
        $platform = $this->platformRepository->findOneBy(['apiKey' => $credentials['token']]);

        if($platform->getResponse() === null)
            throw new \Exception($this->translator->trans(Constants::MSG_401_0000), 401);

        return new AuthenticatedApp($platform->getResponse());
    }

    public function checkCredentials($credentials, UserInterface $player)
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
