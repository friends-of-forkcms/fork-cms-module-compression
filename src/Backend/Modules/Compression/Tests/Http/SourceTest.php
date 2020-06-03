<?php

namespace Backend\Modules\Compression\Tests\Http;

use Backend\Modules\Compression\Exception\InvalidResponseException;
use Backend\Modules\Compression\Exception\ResponseErrorException;
use Backend\Modules\Compression\Http\Source;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class SourceTest
 * @package Backend\Modules\Compression\Tests\Http
 */
class SourceTest extends TestCase
{
    /** @var Client|MockObject  */
    public $client;

    public function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        //$this->dummyFile = __DIR__ . '/TestAssets/dummy.png';
    }

    public function testAnErrorIsThrownForResponseWithEmptyBody(): void
    {
        $body = $this->createMock(StreamInterface::class);
        $body->method('getSize')->willReturn(0);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($body);

        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionMessage('Response body is empty');
        Source::fromResponse($this->client, $response);
    }

    public function testAnErrorIsThrownForInvalidJsonInResponse(): void
    {
        $body = $this->createMock(StreamInterface::class);
        $body->method('getSize')->willReturn(1);
        $body->method('getContents')->willReturn('This:Is:Invalid:Json:[Response]');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($body);

        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionMessageRegExp('/Response body json decoding failed with error `(.*?)`/');
        Source::fromResponse($this->client, $response);
    }

    public function testAnErrorIsThrownFormResponseWithErrorField(): void
    {
        $body = $this->createMock(StreamInterface::class);
        $body->method('getSize')->willReturn(1);
        $body->method('getContents')->willReturn('{"error":"Unauthorized","message":"Credentials are invalid"}');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(401);
        $response->method('getBody')->willReturn($body);

        $this->expectException(ResponseErrorException::class);
        Source::fromResponse($this->client, $response);
    }

    public function testResponseWithProperResponse(): void
    {
        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())->method('getSize')->willReturn(2);
        $body
            ->expects($this->once())
            ->method('getContents')
            ->willReturn(file_get_contents(__DIR__ . '/../TestAssets/example_response.json'));

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($body);
        $response->method('getHeaderLine')->with('Location')->willReturn('http://location/');

        $parent = $this;
        $response = Source::fromResponse($this->client, $response);
        (function () use ($parent) {
            $parent->assertSame($parent->client, $this->client);
            $parent->assertSame('http://location/', $this->url);
        })->call($response);

        $this->assertEquals(761862, $response->getInputSize());
        $this->assertEquals(371526, $response->getOutputSize());
    }

    public function testGetImageWithInvalidLocation(): void
    {
        $response = new Response(400);
        $this->client->method('request')->with('get', 'http://location/')->willReturn($response);
        $source = new Source($this->client, 'http://location/', 1, 1);

        $this->expectException(ResponseErrorException::class);
        $source->getImage();
    }

    public function testGetImage(): void
    {
        $data = random_bytes(32);

        $response = new Response(200, [], $data);
        $this->client->method('request')->with('get', 'http://location/')->willReturn($response);
        $source = new Source($this->client, 'http://location/', 1, 1);

        $image = $source->getImage();
        $this->assertEquals($data, $image->getData());
    }

    public function testToFile(): void
    {
        $data = random_bytes(32);
        $path = tempnam(sys_get_temp_dir(), 'tinypng-php');
        $response = new Response(200, [], $data);
        $this->client->method('request')->with('get', 'http://location/')->willReturn($response);
        $source = new Source($this->client, 'http://location/', 1, 1);

        $source->toFile($path);
        $this->assertEquals($data, file_get_contents($path));
    }

    public function testGetInputSize(): void
    {
        $source = new Source($this->client, 'http://location/', 761862, 371526);
        $this->assertEquals(761862, $source->getInputSize());
    }

    public function testGetOutputSize(): void
    {
        $source = new Source($this->client, 'http://location/', 761862, 371526);
        $this->assertEquals(371526, $source->getOutputSize());
    }

    public function testGetSavedBytes(): void
    {
        $source = new Source($this->client, 'http://location/', 761862, 371526);
        $this->assertEquals(390336, $source->getSavedBytes());
    }

    public function testGetSavedPercentage(): void
    {
        $source = new Source($this->client, 'http://location/', 761862, 371526);
        $this->assertEquals(51, $source->getSavedPercentage());
    }
}
