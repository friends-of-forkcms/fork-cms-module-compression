<?php
declare(strict_types=1);

namespace Backend\Modules\Compression\Http;

use Backend\Modules\Compression\Exception\InvalidResponseException;
use Backend\Modules\Compression\Exception\ResponseErrorException;
use Backend\Modules\Compression\Exception\TooManyRequestsException;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class SourceImage
 * @package Backend\Modules\Compression\Http
 */
class Source
{
    /** @var ClientInterface */
    private $client;

    /** @var string */
    private $url;

    /** @var int */
    private $inputSize;

    /** @var int */
    private $outputSize;

    /**
     * Get source from response
     * @param ClientInterface $client
     * @param ResponseInterface $response
     * @return static
     * @throws InvalidResponseException
     * @throws ResponseErrorException
     */
    public static function fromResponse(ClientInterface $client, ResponseInterface $response): self
    {
        $size = $response->getBody()->getSize();
        if (null !== $size && $size < 1) {
            throw new InvalidResponseException('Response body is empty');
        }

        $body = $response->getBody()->getContents();
        $json = json_decode($body, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidResponseException(
                sprintf('Response body json decoding failed with error `%s`', json_last_error_msg())
            );
        }

        if (!empty($json['error']) && $json['error'] === 'TooManyRequests') {
            throw new TooManyRequestsException($json['message'], $body);
        }

        if (!empty($json['error'])) {
            throw new ResponseErrorException('Response error occurred', $body);
        }

        return new self(
            $client,
            $response->getHeaderLine('Location'),
            $json['input']['size'],
            $json['output']['size']
        );
    }

    public function __construct(ClientInterface $client, string $url, int $inputSize, int $outputSize)
    {
        $this->client = $client;
        $this->url = $url;
        $this->inputSize = $inputSize;
        $this->outputSize = $outputSize;
    }

    public function getImage(): Image
    {
        // Get actual image contents from TinyPNG API by loading the redirect url.
        $response = $this->client->request('get', $this->url);
        if ($response->getStatusCode() !== 200) {
            $contents = $response->getBody()->getContents();
            throw new ResponseErrorException(
                sprintf('Invalid image response (%s)', $contents),
                $contents
            );
        }

        return new Image($response->getBody());
    }

    public function getInputSize(): int
    {
        return $this->inputSize;
    }

    public function getOutputSize(): int
    {
        return $this->outputSize;
    }

    public function getSavedBytes(): int
    {
        return ($this->getInputSize() - $this->getOutputSize());
    }

    public function getSavedPercentage(): int
    {
        $outputRatio = $this->getOutputSize() / $this->getInputSize();
        return (int) (100 - (100 * $outputRatio));
    }

    public function toFile(string $filename): void
    {
        $this->getImage()->toFile($filename);
    }
}
