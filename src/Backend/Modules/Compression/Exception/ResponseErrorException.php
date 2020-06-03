<?php
declare(strict_types=1);

namespace Backend\Modules\Compression\Exception;

use Exception;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ResponseErrorException
 * @package Backend\Modules\Compression\Exception
 */
class ResponseErrorException extends Exception
{
    /** @var string|null */
    private $responseBody;

    public function __construct(string $message = '', $responseBody = null)
    {
        parent::__construct($message, 0, null);

        if ($responseBody instanceof ResponseInterface) {
            $responseBody = $responseBody->getBody()->getContents();
        }

        $this->responseBody = $responseBody;
    }

    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }
}
