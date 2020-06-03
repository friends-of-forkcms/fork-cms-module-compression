<?php
declare(strict_types=1);

namespace Backend\Modules\Compression\Domain\CompressionHistory;

/**
 * Class CompressionHistoryRecordDataTransferObject
 * @package Backend\Modules\Compression\Domain\CompressionHistory
 */
class CompressionHistoryRecordDataTransferObject
{
    /**
     * @var CompressionHistory
     */
    private $compressionHistoryEntity;

    /** @var string */
    public $filename;

    /** @var string */
    public $path;

    /** @var int */
    public $originalSize;

    /** @var int */
    public $compressedSize;

    /** @var string */
    public $checksum;

    public function __construct(CompressionHistory $compressionHistoryEntity = null)
    {
        $this->compressionHistoryEntity = $compressionHistoryEntity;
    }

    public function setCompressionHistoryEntity(CompressionHistory $compressionHistoryEntity): void
    {
        $this->compressionHistoryEntity = $compressionHistoryEntity;
    }
}
