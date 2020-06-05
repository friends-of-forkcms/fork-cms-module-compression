<?php
declare(strict_types=1);

namespace Backend\Modules\Compression\Installer;

use Backend\Core\Engine\Model;
use Backend\Core\Installer\ModuleInstaller;
use Backend\Modules\Compression\Domain\CompressionHistory\CompressionHistory;
use Backend\Modules\Compression\Domain\CompressionSetting\CompressionSetting;

class Installer extends ModuleInstaller
{
    public function install(): void
    {
        $this->addModule('Compression');
        $this->importLocale(__DIR__ . '/Data/locale.xml');
        $this->configureEntities();
        $this->configureSettings();
        $this->configureBackendNavigation();
        $this->configureBackendRights();
        $this->configureDashboardWidget();
    }

    private function configureEntities(): void
    {
        Model::get('fork.entity.create_schema')->forEntityClass(CompressionSetting::class);
        Model::get('fork.entity.create_schema')->forEntityClass(CompressionHistory::class);
    }

    private function configureSettings(): void
    {
        // No default settings needed.
    }

    private function configureBackendNavigation(): void
    {
        // Set navigation for "Settings"
        $navigationSettingsId = $this->setNavigation(null, 'Settings');
        $navigationModulesId = $this->setNavigation($navigationSettingsId, 'Modules');
        $this->setNavigation($navigationModulesId, $this->getModule(), 'compression/settings');

        // Set navigation for "Modules"
        $navigationModulesId = $this->setNavigation(null, 'Modules');
        $this->setNavigation(
            $navigationModulesId,
            $this->getModule(),
            'compression/compression_panel'
        );
    }

    private function configureBackendRights(): void
    {
        $this->setModuleRights(1, $this->getModule());
        $this->setActionRights(1, $this->getModule(), 'CompressionPanel');
        $this->setActionRights(1, $this->getModule(), 'Settings');
        $this->setActionRights(1, $this->getModule(), 'CompressImages');
    }

    /**
     * Insert an empty admin dashboard sequence
     */
    private function configureDashboardWidget(): void
    {
        $this->insertDashboardWidget($this->getModule(), 'Statistics');
    }
}
