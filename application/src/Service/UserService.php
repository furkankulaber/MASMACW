<?php
namespace App\Service;


use App\Entity\Platform;
use App\Entity\User;
use App\Entity\UserDevice;
use App\Exception\ApiException;
use App\Repository\UserDeviceRepository;
use App\Repository\UserRepository;
use App\Repository\RepositoryResponse;
use App\Service\ManagerService\AuthenticationManager;
use App\Service\ManagerService\TokenManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Psr\Container\ContainerInterface;

/**
 * Class UserService
 * @package App\Service
 */
class UserService
{

    /** @var EntityManagerInterface */
    protected EntityManagerInterface $em;

    /** @var ContainerInterface */
    protected ContainerInterface $container;

    /** @var ApiException */
    protected ApiException $exceptionService;

    /** @var UserRepository */
    protected UserRepository $userRepository;

    /** @var UserDeviceRepository */
    protected UserDeviceRepository $userDevicesRepo;

    /**
     * UserService constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container)
            ->setEm($this->container->get('doctrine')->getManager())
            ->setUserRepository($this->getEm()->getRepository(User::class))
            ->setUserDevicesRepo($this->getEm()->getRepository(UserDevice::class));
    }

    /**
     * @param EntityManagerInterface $em
     * @return UserService
     */
    protected function setEm(EntityManagerInterface $em): UserService
    {
        $this->em = $em;
        return $this;
    }

    /**
     * @param ContainerInterface $container
     * @return UserService
     */
    protected function setContainer(ContainerInterface $container): UserService
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @param ApiException $exceptionService
     * @return UserService
     */
    protected function setExceptionService(ApiException $exceptionService): UserService
    {
        $this->exceptionService = $exceptionService;
        return $this;
    }

    /**
     * @param UserRepository $userRepository
     * @return UserService
     */
    protected function setUserRepository(UserRepository $userRepository): UserService
    {
        $this->userRepository = $userRepository;
        return $this;
    }

    /**
     * @param UserDeviceRepository $userDevicesRepo
     * @return UserService
     */
    protected function setUserDevicesRepo(UserDeviceRepository $userDevicesRepo): UserService
    {
        $this->userDevicesRepo = $userDevicesRepo;
        return $this;
    }

    /**
     * @return EntityManagerInterface
     */
    protected function getEm(): EntityManagerInterface
    {
        return $this->em;
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return ApiException
     */
    protected function getExceptionService(): ApiException
    {
        return $this->exceptionService;
    }

    /**
     * @return UserRepository
     */
    protected function getUserRepository(): UserRepository
    {
        return $this->userRepository;
    }

    /**
     * @return UserDeviceRepository
     */
    protected function getUserDevicesRepo(): UserDeviceRepository
    {
        return $this->userDevicesRepo;
    }


    /**
     * @param $data
     * @param string $device
     * @param Platform $platform
     * @return RepositoryResponse
     */
    public function createUserWithDevice($data, string $device, Platform $platform): ServiceResponse
    {
        $userDeviceResponse = $this->getUserOfDevice($device, $platform);
        if ($userDeviceResponse->getResponse() instanceof UserDevice) {
            return new ServiceResponse($userDeviceResponse->getResponse()->getUser());
        }

        $faker = Factory::create('tr_TR');
        $userDevice = $this->createUserDevice($device,$platform,$data['language']);
        $insertUser = [
            'alias' => $faker->userName,
            'email' => $faker->email,
            'password' => $faker->password,
            'status' => 'a',
            'userDevice' => $userDevice,
            'createdAt' => new \DateTime('now',new \DateTimeZone('-6'))
        ];
        $insertResponse = $this->getUserRepository()->insert($insertUser);
        if(!$insertResponse->getResponse() instanceof User)
        {
            return new ServiceResponse($insertResponse->getException());
        }

        return new ServiceResponse($insertResponse->getResponse());
    }

    public function createToken( $user, TokenManager $tokenManager)
    {
        return $tokenManager->createToken($user);
    }

    /**
     * @param $user
     * @param Platform $platform
     *
     * @return RepositoryResponse
     */
    private function getUserOfDevice($user, Platform $platform): RepositoryResponse
    {
        return $this->userDevicesRepo->userOfDevice($platform->getId(), $user);
    }

    private function createUserDevice($device,$platform,$language): UserDevice
    {
        $userDevice = new UserDevice();
        $userDevice->setDevice($device)
            ->setLanguage($language)
            ->setPlatform($platform);

        return $userDevice;
    }
}
