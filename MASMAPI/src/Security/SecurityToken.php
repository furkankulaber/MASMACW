<?php


namespace App\Security;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class SecurityToken extends AbstractToken
{
    public function __construct(array $roles)
    {
        $this->setAuthenticated(count($roles) > 0);

        parent::__construct($roles);
    }


    /**
     * Returns the user credentials.
     *
     * @return mixed The user credentials
     */
    public function getCredentials()
    {
        // TODO: Implement getCredentials() method.
    }
}
