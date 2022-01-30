<?php
namespace App\Exception;


use Throwable;

class ApiCustomException extends \Exception
{
    private string $internalCode;

    private array $replacements;

    /**
     * ApiCustomException constructor.
     * @param int $statusCode
     * @param string|null $message
     * @param string $internalCode
     * @param Throwable|null $previous
     */
    public function __construct($message = null, $statusCode = 0, $internalCode = "", $replacements = [], Throwable $previous = null)
    {
        $this->internalCode = $internalCode;
        $this->replacements = $replacements;
        parent::__construct($message,$statusCode,$previous);
    }

    /**
     * @return string|null
     */
    public function getInternalCode(): ?string
    {
        return $this->internalCode;
    }

    /**
     * @return array|null
     */
    public function getReplacements(): mixed
    {
        return $this->replacements;
    }

}
