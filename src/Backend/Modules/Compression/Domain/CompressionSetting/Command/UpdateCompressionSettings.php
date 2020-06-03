<?php
declare(strict_types=1);

namespace Backend\Modules\Compression\Domain\CompressionSetting\Command;

/**
 * Class UpdateCompressionSettings
 * @package Backend\Modules\Compression\Domain\CompressionSetting\Command
 */
final class UpdateCompressionSettings
{
    /**
     * @var string|null
     */
    public $folders;

    /**
     * @return array
     */
    public function getFoldersArray(): array
    {
        return $this->folders !== null ? explode(',', $this->folders) : [];
    }
}
