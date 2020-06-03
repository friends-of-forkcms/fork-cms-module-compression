<?php

namespace Backend\Modules\Compression\Domain\CompressionHistory\Event;

/**
 * Class CompressionHistoryRecordCreated
 * @package Backend\Modules\Compression\Domain\CompressionHistory\Event
 */
final class CompressionHistoryRecordCreated
{
    /**
     * @var string The name the listener needs to listen to to catch this event.
     */
    public const EVENT_NAME = 'compression.event.compression_history_record_created';
}
