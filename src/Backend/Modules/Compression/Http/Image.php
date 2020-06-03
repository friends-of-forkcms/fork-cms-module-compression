<?php
declare(strict_types=1);

namespace Backend\Modules\Compression\Http;

use Psr\Http\Message\StreamInterface;
use Symfony\Component\Translation\Exception\InvalidResourceException;

/**
 * Class Image
 * @package Backend\Modules\Compression\Http
 */
class Image
{
    /** @var StreamInterface */
    private $dataStream;

    public function __construct(StreamInterface $dataStream)
    {
        $this->dataStream = $dataStream;
    }

    public function getDataStream(): StreamInterface
    {
        return $this->dataStream;
    }

    public function getData(): string
    {
        return $this->getDataStream()->getContents();
    }

    public function __toString()
    {
        return $this->getData();
    }

    public function toFile(string $fileName): void
    {
        $resource = @fopen($fileName, 'wb');
        if (!$resource) {
            throw new InvalidResourceException("Resource $fileName does not exists or no permissions to the folder");
        }

        $this->getDataStream()->rewind();
        while (!$this->getDataStream()->eof()) {
            fwrite($resource, $this->getDataStream()->read(1024));
        }

        fclose($resource);
    }
}
