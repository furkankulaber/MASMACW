<?php


namespace App\Service\ResponseService;

use App\Entity\User;
use App\Entity\UserSession;
use App\Security\Authenticated;
use App\Service\ResponseService\Utilities\ApiResponse;
use App\Service\ResponseService\Utilities\ApiResult;
use App\Traits\Serialize;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class Service
{
    use Serialize;

    /** @var TranslatorInterface  */
    private TranslatorInterface $translator;

    /** @var ContainerInterface  */
    private ContainerInterface $container;

    /** @var Security  */
    private Security $security;

    /** @var null|\Exception  */
    private ?\Exception $exception = null;

    /** @var null|string */
    private ?string $sessionToken = null;

    public function __construct(ContainerInterface $container, TranslatorInterface $translator, Security $security)
    {
        $this->translator = $translator;
        $this->container = $container;
        $this->security = $security;
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    private function identifyCode(string $code): array
    {
        list($msgIdentifier, $statusCode, $statusSubCode) = explode('.', $code);

        return array(
            'httpStatusCode' => (int) $statusCode,
            'code' => implode('.', [$statusCode, $statusSubCode])
        );
    }

    /**
     * @param ResponseInterface|array|int|string|null|object $result
     * @param string|null $code
     * @param string|null $message
     * @param array $replacements
     * @return JsonResponse
     */
    public function toJsonResponse(
        $result = null,
        ?string $code = null,
        ?string $message = null,
        array $replacements = []
    ): JsonResponse
    {
        $code = $code ?? Constants::MSG_200_0000;
        $message = $message ?? $this->getTranslator()->trans($code, $replacements, 'messages',$this->getLocaleFromStorage());

        $identifiedStatusCode = $this->identifyCode($code);


        $apiResult = new ApiResult($this->prepareDataForJsonResponse($result));
        $apiResponse = new ApiResponse($identifiedStatusCode['code'], $message, $apiResult, $this->sessionToken);
        return new JsonResponse($apiResponse->outputToArray(), $identifiedStatusCode['httpStatusCode']);
    }

    private function getLocaleFromStorage()
    {
        return 'tr-tr';
    }

    private function prepareDataForJsonResponse($data,$recursive=false)
    {
        if ($data instanceof ResponseInterface) {
            return $data->outputToArray();
        }
        if(is_object($data)){
            return json_decode($this->getSerializer()->serialize($data,'json', [
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]));
        }

        if (is_array($data)) {
            $response = [];
            foreach ($data as $k => $v) {
                $response[$k] = $this->prepareDataForJsonResponse($v,true);
            }

            return $response;
        }
        if($recursive === true ){
            return $data;
        }
        if($data === null){
            return $data;
        }
        return ['value' => $data];
    }

    /**
     * @param string $sessionToken
     * @return $this
     */
    public function withSessionToken(string $sessionToken): Service
    {
        $this->sessionToken = $sessionToken;
        return $this;
    }
}
