<?php

namespace App\Service\ManagerService;

use App\Entity\User;
use App\Entity\UserDevice;
use App\Entity\Platform;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AuthenticationManager
{
    /** @var ContainerInterface  */
    private ContainerInterface $container;

    /** @var mixed|string  */
    private string $salt;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $authParams = $container->getParameter('AUTH_PARAMS');
        $this->salt = $authParams['SALT'];
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @param string $deviceId
     * @param Platform $platform
     * @return bool|User
     */
    public function loginDeviceId(string $deviceId, Platform $platform)
    {

        /** @var null|UserDevice $userOfDevices */
        $userOfDevices = $this->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository(UserDevice::class)->findOneBy(['device' => $deviceId, 'platform' => $platform, 'status' => 'a']);
        if ($userOfDevices->getResponse() instanceof UserDevice) {
           if($userOfDevices->getResponse()->getUser() instanceof  User){
               return $userOfDevices->getResponse()->getUser();
           }
        }

        return false;
    }
}
