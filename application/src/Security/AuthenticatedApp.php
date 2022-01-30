<?php

namespace App\Security;

use App\Entity\Platform;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticatedApp implements UserInterface
{
    /** @var Platform  */
    private Platform $platform;

    /** @var array  */
    private array $roles = [];

    public function __construct(Platform $platform)
    {
        $this->setPlatform($platform)->setRoles(['ROLE_AUTHENTICATED_PLATFORM']);
    }

    /**
     * @return Platform
     */
    public function getPlatform(): Platform
    {
        return $this->platform;
    }

    /**
     * @param  Platform  $platform
     *
     * @return AuthenticatedApp
     */
    public function setPlatform(Platform $platform): AuthenticatedApp
    {
        $this->platform = $platform;
        return $this;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param  array  $roles
     *
     * @return AuthenticatedApp
     */
    public function setRoles(array $roles): AuthenticatedApp
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword()
    {
        // TODO: Implement getPassword() method.
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    public function getUsername(): string
    {
        return sprintf(
            '%s-%s',
            $this->getPlatform()->getCode(),
            $this->getPlatform()->getApiKey()
        );
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }
}
