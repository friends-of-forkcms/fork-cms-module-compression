<?php
declare(strict_types=1);

namespace Backend\Modules\Compression\Domain\CompressionSetting\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class CompressionSettingsUpdated
 * @package Backend\Modules\Compression\Domain\CompressionSetting\Event
 */
final class CompressionSettingsUpdated extends Event
{
    /**
     * @var string The name the listener needs to listen to to catch this event.
     */
    public const EVENT_NAME = 'compression.event.compression_settings_updated';
}
