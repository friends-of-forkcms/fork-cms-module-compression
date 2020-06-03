<?php
declare(strict_types=1);

namespace Backend\Modules\Compression\Domain\Settings\Event;

use Backend\Modules\Compression\Domain\Settings\Command\SaveSettings;
use Symfony\Component\EventDispatcher\Event;

/**
 * Compression module settings saved Event
 */
final class SettingsSavedEvent extends Event
{
    /**
     * @var string The name the listener needs to listen to to catch this event.
     */
    public const EVENT_NAME = 'compression.event.settings_saved';

    /**
     * @var SaveSettings
     */
    protected $settings;

    public function __construct(SaveSettings $settings)
    {
        $this->settings = $settings;
    }

    public function getSettings(): SaveSettings
    {
        return $this->settings;
    }
}
