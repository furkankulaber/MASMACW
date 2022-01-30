<?php


namespace App\Controller;


use App\Entity\Platform;
use App\Entity\User;
use App\Entity\UserSession;
use App\Exception\ApiCustomException;
use App\Security\Authenticated;
use App\Service\ResponseService\Constants;
use App\Service\ResponseService\Service as ResponseService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Exception\ApiException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class CoreController extends AbstractController
{

    const STATUS_ACTIVE = 'a';

    /** @var Request */
    private Request $request;

    public ApiException $exceptionService;

    public $container;

    /** @var ResponseService */
    private ResponseService $responseService;

    /** @var ValidatorInterface */
    private ValidatorInterface $validator;

    /** @var string|null */
    private $xDeviceId = null;

    /** @var string|null */
    private $platform = null;

    private EntityManagerInterface $entityManager;

    private $data;

    public $locale = 'tr-tr';


    /**
     * CoreController constructor.
     * @param RequestStack $requestStack
     * @param ResponseService $responseService
     */
    public function __construct(RequestStack $requestStack, ResponseService $responseService, ContainerInterface $container)
    {
        if (null === $requestStack->getMasterRequest()) {
            throw new RuntimeException($this->getResponseService()->getTranslator()->trans(Constants::MSG_400_0000));
        }
        $this->container = $container;
        $this->request = $requestStack->getMasterRequest();
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->responseService = $responseService;
        $this->data = json_decode($this->getRequest()->getContent(), false);
        $this->xDeviceId = $this->getRequest()->headers->has('X-Device-Id') ? $this->getRequest()->headers->get('X-Device-Id') : null;
        $this->platformKey = $this->getRequest()->headers->has('X-Api-Key') ? $this->getRequest()->headers->get('X-Api-Key') : null;
        $this->validator = Validation::createValidator();
    }

    /**
     * @return Request
     */
    public function getRequest(): ?Request
    {
        return $this->request;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return ResponseService
     */
    public function getResponseService(): ResponseService
    {
        return $this->responseService;
    }

    /**
     * @return ValidatorInterface
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    /**
     * @return string|null
     */
    public function getXDeviceId(): ?string
    {
        return $this->xDeviceId;
    }

    /**
     * @return string|null
     */
    public function getPlatformKey(): ?string
    {
        return $this->platformKey;
    }

    /**
     * @return Platform|null
     * @throws ApiCustomException
     */
    public function getPlatform(): ?Platform
    {
        $platform = ($this->container->get('doctrine')->getRepository(Platform::class))->findOneBy(['apiKey' => $this->getPlatformKey()]);
        if (!$platform->getResponse() instanceof Platform) {
            throw new ApiCustomException(null, null, Constants::MSG_401_0000);
        }

        return $platform->getResponse();
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @throws ApiCustomException
     */
    public function checkValidate($key, $constraint, $singleKey = null): void
    {
        $violations = $this->getValidator()->validate($key, $constraint);
        if ($violations->count() > 0) {
            $formatedViolationList = [];
            for ($i = 0; $i < $violations->count(); $i++) {
                $violation = $violations->get($i);
                if ($violation->getInvalidValue() === null) {
                    if (array_key_exists('X-Api-Key', $key) && is_null($key['X-Api-Key'])) {
                        throw new ApiCustomException(null, 0, Constants::MSG_401_0000);
                    }
                    if (array_key_exists('X-Device-Id', $key) && is_null($key['X-Device-Id'])) {
                        throw new ApiCustomException(null, 0, Constants::MSG_412_0007 , ['invalidPropertyName' => 'X-Device-Id']);
                    }
                    $message = is_array($key) ? ucfirst(substr($violation->getPropertyPath(), 1, -1)) : $singleKey;
                    throw new ApiCustomException(null, 0, Constants::MSG_412_9995, ['invalidPropertyName' => $message]);
                }
                if (is_array($key)) {
                    $formatedViolationList[] = ucfirst(substr($violation->getPropertyPath(), 1, -1));
                } else {
                    $formatedViolationList[] = $singleKey;
                }
            }
            $formatedViolationListText = implode(", ", array_unique($formatedViolationList));

            throw new ApiCustomException(null, 0, Constants::MSG_412_9999, ['invalidPropertyName' => $formatedViolationListText]);

        }
    }

    /**
     * @return User|null
     */
    protected function getUser(): ?User
    {
        /** @var TokenStorageInterface $tokenStorage */
        $tokenStorage = $this->container->get('security.token_storage');
        if (null !== $tokenStorage->getToken() && $tokenStorage->getToken()->getUser() instanceof Authenticated && $tokenStorage->getToken()->getUser()->getSession() instanceof UserSession) {
            return $tokenStorage->getToken()->getUser()->getSession()->getUser();
        }

        return null;
    }
}
