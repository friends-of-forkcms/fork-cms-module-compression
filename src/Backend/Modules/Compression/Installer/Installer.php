<?php

namespace Backend\Modules\Compression\Installer;

use Backend\Core\Installer\ModuleInstaller;

/**
 * Installer for the Compression module
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class Installer extends ModuleInstaller
{
    /**
     * Insert an empty admin dashboard sequence
     */
    private function addDashboardWidget(): void
    {
        $compressionWidget = array(
            'column' => 'middle',
            'position' => 2,
            'hidden' => false,
            'present' => true
        );

        $this->insertDashboardWidget($this->getModule(), 'Statistics', $compressionWidget);
    }

    /**
     * Install the module
     */
    public function install(): void
    {
        $this->addModule('Compression');

        $this->importSQL(dirname(__FILE__) . '/Data/install.sql');
        $this->importLocale(dirname(__FILE__) . '/Data/locale.xml');

        $this->setModuleRights(1, $this->getModule());
        $this->setActionRights(1, $this->getModule(), 'Settings');

        // settings navigation
        $navigationSettingsId = $this->setNavigation(null, 'Settings');
        $navigationModulesId = $this->setNavigation($navigationSettingsId, 'Modules');
        $this->setNavigation($navigationModulesId, 'Compression', 'compression/settings');

        // install dashboardwidget
        $this->addDashboardWidget();
    }
}
