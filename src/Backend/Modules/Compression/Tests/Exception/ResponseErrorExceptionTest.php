<?php

namespace Backend\Modules\Compression\Tests\Exception;

use Backend\Modules\Compression\Exception\ResponseErrorException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class ResponseErrorExceptionTest
 * @package Backend\Modules\Compression\Tests\Exception
 */
class ResponseErrorExceptionTest extends TestCase
{
    public function testExceptionProvidesResponseBodyProvidedByString(): void
    {
        $exception = new ResponseErrorException('Message', 'Response body');
        $this->assertSame('Response body', $exception->getResponseBody());
    }

    public function testExceptionProvidesResponseBodyProvidedByResponseInterface(): void
    {
        $body = $this->createMock(StreamInterface::class);
        $body->method('getContents')->willReturn('Response body');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($body);

        $exception = new ResponseErrorException('Message', $response);
        $this->assertSame('Response body', $exception->getResponseBody());
    }
}
