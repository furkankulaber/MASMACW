<?php


namespace App\Service\ResponseService\Utilities;


use App\Service\ResponseService\ResponseInterface;

class ApiResult implements ResponseInterface
{



    private $set;

    /**
     * @param $set
     */
    public function __construct($set)
    {
        $this->set = $set;
    }

    /**
     * @return mixed
     */
    public function getSet()
    {
        return $this->set;
    }

    /**
     * @param mixed $set
     * @return ApiResult
     */
    public function setSet($set)
    {
        $this->set = $set;
        return $this;
    }

    public function outputToArray(): array
    {
        return array(
            'set' => $this->getSet()
        );
    }
}
