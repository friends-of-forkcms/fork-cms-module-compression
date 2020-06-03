<?php
declare(strict_types=1);

namespace Backend\Modules\Compression\Http;

use Backend\Modules\Compression\Exception\FileNotFoundException;
use Backend\Modules\Compression\Exception\ValidateResponseErrorException;
use Common\ModulesSettings;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

/**
 * Class TinyPngApiClient
 * @package Backend\Modules\Compression\Clients
 */
class TinyPngApiClient
{
    private const VERSION  = '0.1.0';
    private const BASE_URL =  'https://api.tinify.com';
    private const API_ROUTE_SHRINK = "/shrink";
    public const MAX_FREE_CREDITS = 500;

    /** @var Client */
    private $client;

    /** @var string */
    private $apiKey;

    public static function createFromModuleSettings(ModulesSettings $settings): self
    {
        return new self($settings->get('Compression', 'api_key', ''));
    }

    public function __construct(string $apiKey, array $options = [], ?ClientInterface $client = null)
    {
        $this->apiKey = $apiKey;

        if ($client === null) {
            $client = new Client([
                'http_errors' => false,
                'base_uri' => self::BASE_URL,
                'headers' => [
                    'User-Agent' => sprintf(
                        'TinyPng/%s Tinify/1.5.2 PHP/%s Guzzle curl/1',
                        self::VERSION,
                        PHP_VERSION
                    ),
                    'Authorization' => sprintf('Basic %s', base64_encode($this->apiKey)),
                    'Content-Type' => 'application/json',
                ]
            ] + $options);
        }

        $this->client = $client;
    }

    public function validate(): bool
    {
        $response = $this->client->request('POST', self::API_ROUTE_SHRINK);

        // If status code is invalid, probably not a valid api key or account limit reached.
        if (!in_array($response->getStatusCode(), [200, 400, 429], true)) {
            throw new ValidateResponseErrorException(
                sprintf('Validation failed (%s)', $response->getBody()->getContents()),
                $response->getBody()->getContents()
            );
        }
        return true;
    }

    public function getMonthlyCompressionCount(): int
    {
        $response = $this->client->request('POST', self::API_ROUTE_SHRINK);
        // If status code is invalid, probably not a valid api key or account limit reached.
        if (
            !$response->hasHeader('Compression-Count') ||
            !in_array($response->getStatusCode(), [200, 400, 429], true)
        ) {
            throw new ValidateResponseErrorException(
                sprintf('Failed to get compression count (%s)', $response->getBody()->getContents()),
                $response->getBody()->getContents()
            );
        }

        return (int) current($response->getHeader('Compression-Count'));
    }

    public function fromBuffer(string $buffer): Source
    {
        $response = $this->client->request('POST', self::API_ROUTE_SHRINK, ['body' => $buffer]);
        return Source::fromResponse($this->client, $response);
    }

    public function fromFile(string $file): Source
    {
        if (!file_exists($file)) {
            throw new FileNotFoundException("File $file not found");
        }

        $content = @file_get_contents($file);
        if (false === $content) {
            throw new FileNotFoundException(
                sprintf('Cannot read `%s` file (%s)', $file, error_get_last()['message'] ?? '')
            );
        }

        return $this->fromBuffer($content);
    }
}
