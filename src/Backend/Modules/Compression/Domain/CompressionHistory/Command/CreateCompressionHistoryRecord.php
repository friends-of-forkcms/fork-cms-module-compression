<?php
declare(strict_types=1);

namespace Backend\Modules\Compression\Domain\CompressionHistory\Command;

use Backend\Modules\Compression\Domain\CompressionHistory\CompressionHistoryRecordDataTransferObject;
use SplFileInfo;

/**
 * Class CreateCompressionHistoryRecord
 * @package Backend\Modules\Compression\Domain\CompressionHistory\Command
 */
final class CreateCompressionHistoryRecord extends CompressionHistoryRecordDataTransferObject
{
    public function __construct(SplFileInfo $file, int $originalSize, int $compressedSize)
    {
        parent::__construct();

        $this->filename = $file->getFilename();
        $this->path = $file->getRealPath();
        $this->originalSize = $originalSize;
        $this->compressedSize = $compressedSize;
        $this->checksum = sha1_file($file->getRealPath());
    }
}
