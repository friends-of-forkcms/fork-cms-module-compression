<?php
declare(strict_types=1);

namespace Backend\Modules\Compression\Domain\CompressionSetting\Command;

use Backend\Modules\Compression\Domain\CompressionSetting\CompressionSetting;
use Backend\Modules\Compression\Domain\CompressionSetting\CompressionSettingRepository;

final class UpdateCompressionSettingsHandler
{
    /**
     * @var CompressionSettingRepository
     */
    private $repository;

    public function __construct(CompressionSettingRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(UpdateCompressionSettings $settings): void
    {
        $folderPaths = $settings->getFoldersArray();

        // Make sure we delete all records that are not checked.
        $this->repository->deleteExceptPaths($folderPaths);

        foreach ($folderPaths as $folderPath) {
            if ($this->repository->exists($folderPath)) {
                continue;
            }

            $compressionSetting = CompressionSetting::createFromPath($folderPath);
            $this->repository->add($compressionSetting);
        }
    }
}
