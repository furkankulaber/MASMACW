<?php

namespace App\Security;

use App\Entity\UserSession;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

class Authenticated implements UserInterface
{
    /** @var UserSession  */
    private UserSession $session;

    /** @var array  */
    private array $roles = [];

    /** @var UserSession|null */
    private ?UserSession $nextSession;

    public function __construct(UserSession $session, array $roles = [], ?UserSession $nextSession = null)
    {
        $this->setRoles(['ROLE_USER'])->setSession($session)->setNextSession($nextSession);
    }

    /**
     * @return UserSession|null
     */
    public function getSession(): ?UserSession
    {
        return $this->session;
    }

    /**
     * @param UserSession $session
     * @return Authenticated
     */
    public function setSession(UserSession $session): Authenticated
    {
        $this->session = $session;
        // $this->setRolesViaUser($session->getUser());

        return $this;
    }

    /**
     * @return UserSession|null
     */
    public function getNextSession(): ?UserSession
    {
        return $this->nextSession;
    }

    /**
     * @param  UserSession|null  $nextSession
     *
     * @return Authenticated
     */
    public function setNextSession(?UserSession $nextSession): Authenticated
    {
        $this->nextSession = $nextSession;
        return $this;
    }

    private function setRolesViaUser(User $user): Authenticated
    {
        $roles = [];

        $this->roles = $roles;
        return $this;
    }

    /**
     * @param array $roles
     * @return Authenticated
     */
    public function setRoles(array $roles): Authenticated
    {
        $this->roles = $roles;
        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function addRole(string $role)
    {
        array_push($this->roles, $role);
        $this->roles = array_unique($this->roles);
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

    public function getUsername()
    {
        return $this->session->getUser()->getUsername();
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }
}
