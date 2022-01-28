<?php

namespace App\Security\Providers;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\Authenticated;
use App\Service\ResponseService\Constants;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthenticatedProvider implements UserProviderInterface
{
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /** @var TranslatorInterface  */
    private TranslatorInterface $translator;

    public function __construct(UserRepository $userRepository, TranslatorInterface $translator)
    {
        $this->userRepository = $userRepository;
        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    public function loadUserByUsername($username)
    {
        $user = $this->userRepository->findOneBy(['email' => $username]);
        if (!$user instanceof User) {
            throw new NotFoundHttpException($this->translator->trans(Constants::MSG_401_0001));
        }

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function refreshUser(SecurityUserInterface $authenticatedApp)
    {
        if (!$authenticatedApp instanceof UserInterface) {
            throw new UnsupportedUserException(sprintf('Invalid app class "%s".', get_class($authenticatedApp)));
        }

        return $authenticatedApp;
    }

    /**
     * @inheritDoc
     */
    public function supportsClass($class)
    {
        return Authenticated::class === $class;
    }
}
