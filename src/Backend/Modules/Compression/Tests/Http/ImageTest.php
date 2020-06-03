<?php

namespace Backend\Modules\Compression\Tests\Http;

use Backend\Modules\Compression\Http\Image;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Translation\Exception\InvalidResourceException;

/**
 * Class ImageTest
 * @package Backend\Modules\Compression\Tests\Http
 */
class ImageTest extends TestCase
{
    /** @var StreamInterface */
    public $stream;

    /** @var string */
    public $streamContent;

    protected function setUp(): void
    {
        $this->streamContent = random_bytes(128);
        $this->stream = $this->createMock(StreamInterface::class);
        $this->stream->method('getContents')->willReturn($this->streamContent);
    }

    public function testCanGetDataStream(): void
    {
        $image = new Image($this->stream);
        $this->assertSame($this->stream, $image->getDataStream());
    }

    public function testCanGetDataAsString(): void
    {
        $image = new Image($this->stream);
        $this->assertSame($this->streamContent, $image->getData());
    }

    public function testCanMapToString(): void
    {
        $image = new Image($this->stream);
        $this->assertSame($this->streamContent, (string) $image);
    }

    public function testCanSaveToFile(): void
    {
        $this->stream->expects($this->once())->method('rewind');
        $this->stream->expects($this->exactly(2))->method('eof')->will($this->onConsecutiveCalls(false, true));
        $this->stream->method('read')->with(1024)->willReturn($this->streamContent);

        $path = tempnam(sys_get_temp_dir(), 'tinypng-php');
        $image = new Image($this->stream);
        $image->toFile($path);

        $this->assertEquals($this->streamContent, file_get_contents($path));
    }

    public function testSaveToFileThrowsExceptionWhenResourceNotAvailable(): void
    {
        $image = new Image($this->stream);

        $this->expectException(InvalidResourceException::class);
        $image->toFile('/path/that/will/not/exist/in/file/system/' . bin2hex(random_bytes(32)));
    }
}
