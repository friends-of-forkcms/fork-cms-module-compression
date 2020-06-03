<?php
declare(strict_types=1);

namespace Backend\Modules\Compression\Tests\Domain\CompressionHistory\Helper;

use Backend\Modules\Compression\Domain\CompressionHistory\Helpers\Helper;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TypeError;

class HelperTest extends TestCase
{
    public function testCanMakeNumericBytesHumanReadable(): void
    {
        $this->assertEquals('1 KB', Helper::readableBytes(1024));
        $this->assertEquals('1000 bytes', Helper::readableBytes(1000));
        $this->assertEquals('9.31 GB', Helper::readableBytes(10000000000));
        $this->assertEquals('648.37 TB', Helper::readableBytes(712893712304234));
        $this->assertEquals('5.52 PB', Helper::readableBytes(6212893712323224));
    }

    public function testShouldOnlyAcceptBytesAsIntegers(): void
    {
        $this->expectException(TypeError::class);
        Helper::readableBytes('1024');
    }

    public function testShouldNotAllowNegativeBytes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Helper::readableBytes(-1);
    }

    public function testShouldAcceptZeroBytes(): void
    {
        $this->assertEquals('0 KB', Helper::readableBytes(0));
    }
}
