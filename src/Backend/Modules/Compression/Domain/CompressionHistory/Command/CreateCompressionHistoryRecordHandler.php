<?php
declare(strict_types=1);

namespace Backend\Modules\Compression\Domain\CompressionHistory\Command;

use Backend\Modules\Compression\Domain\CompressionHistory\CompressionHistory;
use Backend\Modules\Compression\Domain\CompressionHistory\CompressionHistoryRepository;

/**
 * Class CreateCompressionHistoryRecordHandler
 * @package Backend\Modules\Compression\Domain\CompressionHistory\Command
 */
final class CreateCompressionHistoryRecordHandler
{
    /** @var CompressionHistoryRepository */
    private $compressionHistoryRepository;

    public function __construct(CompressionHistoryRepository $compressionHistoryRepository)
    {
        $this->compressionHistoryRepository = $compressionHistoryRepository;
    }

    public function handle(CreateCompressionHistoryRecord $createCompressionHistoryRecord): void
    {
        $historyRecord = CompressionHistory::fromDataTransferObject($createCompressionHistoryRecord);
        $this->compressionHistoryRepository->add($historyRecord);

        // We redefine the record, so we can use it in an action
        $createCompressionHistoryRecord->setCompressionHistoryEntity($historyRecord);
    }
}
