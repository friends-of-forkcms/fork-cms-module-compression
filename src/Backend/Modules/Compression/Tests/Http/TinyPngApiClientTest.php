<?php

namespace Backend\Modules\Compression\Tests\Http;

use Backend\Modules\Compression\Exception\FileNotFoundException;
use Backend\Modules\Compression\Exception\ValidateResponseErrorException;
use Backend\Modules\Compression\Http\Source;
use Backend\Modules\Compression\Http\TinyPngApiClient;
use Common\ModulesSettings;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * Class TinyPngApiClientTest
 * @package Backend\Modules\Compression\Tests\Http
 */
class TinyPngApiClientTest extends TestCase
{
    /**
     * @var Client
     */
    public $guzzle;

    public function setUp(): void
    {
        $this->guzzle = $this->createMock(Client::class);
    }

    public function testSetDefaultClient(): void
    {
        $client = new TinyPngApiClient('');
        (function () {
            TinyPngApiClientTest::assertInstanceOf(Client::class, $this->client);
        })->call($client);
    }

    public function testSetClientInConstructor(): void
    {
        $guzzle = $this->guzzle;
        $client = new TinyPngApiClient('', [], $guzzle);
        (function () use ($guzzle) {
            TinyPngApiClientTest::assertSame($guzzle, $this->client);
        })->call($client);
    }

    public function testSetApiKeyIsStored(): void
    {
        $key = random_bytes(32);
        $client = new TinyPngApiClient($key);
        (function () use ($key) {
            TinyPngApiClientTest::assertSame($key, $this->apiKey);
        })->call($client);
    }

    public function testClientIsCreatedFromModuleSettings(): void
    {
        $key = random_bytes(32);
        $modulesSettings = $this->createMock(ModulesSettings::class);
        $modulesSettings->method('get')->with('Compression', 'api_key')->willReturn($key);

        $client = TinyPngApiClient::createFromModuleSettings($modulesSettings);
        (function () use ($key) {
            TinyPngApiClientTest::assertSame($key, $this->apiKey);
        })->call($client);
    }

    public function testCanGetValidateToRun(): void
    {
        $response = new Response(200);
        $this->guzzle->method('request')->with('POST', '/shrink')->willReturn($response);

        $key = random_bytes(32);
        $client = new TinyPngApiClient($key, [], $this->guzzle);

        $this->assertTrue($client->validate());
    }

    public function testValidateWithValidKeyShouldReturnTrue(): void
    {
        $body = '{"error":"Input missing","message":"No input"}';
        $response = new Response(400, [], $body);
        $this->guzzle->method('request')->with('POST', '/shrink')->willReturn($response);

        $key = random_bytes(32);
        $client = new TinyPngApiClient($key, [], $this->guzzle);

        $this->assertTrue($client->validate());
    }

    public function testValidateWithLimitedKeyShouldReturnTrue(): void
    {
        $body = '{"error":"Too many requests","message":"Your monthly limit has been exceeded"}';
        $response = new Response(429, [], $body);
        $this->guzzle->method('request')->with('POST', '/shrink')->willReturn($response);

        $key = random_bytes(32);
        $client = new TinyPngApiClient($key, [], $this->guzzle);

        $this->assertTrue($client->validate());
    }

    public function testValidateWithErrorShouldThrowException(): void
    {
        $body = '{"error":"Unauthorized","message":"Credentials are invalid"}';
        $response = new Response(401, [], $body);
        $this->guzzle->method('request')->with('POST', '/shrink')->willReturn($response);

        $key = random_bytes(32);
        $client = new TinyPngApiClient($key, [], $this->guzzle);

        $this->expectException(ValidateResponseErrorException::class);
        $client->validate();
    }

    public function testCanGetMonthlyCompressionCount(): void
    {
        $response = new Response(200, ['Compression-Count' => '187']);
        $this->guzzle->method('request')->with('POST', '/shrink')->willReturn($response);

        $key = random_bytes(32);
        $client = new TinyPngApiClient($key, [], $this->guzzle);

        $this->assertEquals(187, $client->getMonthlyCompressionCount());
    }

    public function testShouldThrowExceptionIfNoMonthlyCompressionCountHeaderAvailable(): void
    {
        $response = new Response(200, []);
        $this->guzzle->method('request')->with('POST', '/shrink')->willReturn($response);

        $key = random_bytes(32);
        $client = new TinyPngApiClient($key, [], $this->guzzle);

        $this->expectException(ValidateResponseErrorException::class);
        $client->getMonthlyCompressionCount();
    }

    public function testShouldThrowExceptionIfBadResponseForMonthlyCompressionCount(): void
    {
        $response = new Response(500, ['Compression-Count' => '187']);
        $this->guzzle->method('request')->with('POST', '/shrink')->willReturn($response);

        $key = random_bytes(32);
        $client = new TinyPngApiClient($key, [], $this->guzzle);

        $this->expectException(ValidateResponseErrorException::class);
        $client->getMonthlyCompressionCount();
    }

    public function testFromBuffer(): void
    {
        $response = new Response(200, [], file_get_contents(__DIR__ . '/../TestAssets/example_response.json'));
        $buffer = random_bytes(128);
        $this->guzzle->method('request')->with('POST', '/shrink', ['body' => $buffer])->willReturn($response);

        $client = new TinyPngApiClient('', [], $this->guzzle);
        $source = $client->fromBuffer($buffer);
        $this->assertInstanceOf(Source::class, $source);
    }

    public function testFromFileWithInvalidFile(): void
    {
        $path = '/path/that/does/not/exist/in/any/system/' . bin2hex(random_bytes(32));

        $client = new TinyPngApiClient('', [], $this->guzzle);

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessageRegExp('/File (.*?) not found/');
        $client->fromFile($path);
    }

    public function testFromFileWithInvalidFileThatCannotBeRead(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'tinypng-php');
        chmod($path, 0);

        $client = new TinyPngApiClient('', [], $this->guzzle);

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessageRegExp('/Cannot read `(.*?)` file \((.*?)\)/');
        $client->fromFile($path);
    }

    public function testFromFile(): void
    {
        $buffer = random_bytes(128);

        $path = tempnam(sys_get_temp_dir(), 'tinypng-php');
        file_put_contents($path, $buffer);

        $response = new Response(200, [], file_get_contents(__DIR__ . '/../TestAssets/example_response.json'));
        $this->guzzle->method('request')->with('POST', '/shrink', ['body' => $buffer])->willReturn($response);

        $client = new TinyPngApiClient('', [], $this->guzzle);
        $source = $client->fromFile($path);
        $this->assertInstanceOf(Source::class, $source);
    }
}
