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
    private function insertWidget()
    {
        $compressionWidget = array(
            'column' => 'middle',
            'position' => 2,
            'hidden' => false,
            'present' => true
        );

        // insert the dashboardwidget
        $this->insertDashboardWidget('Compression', 'Statistics', $compressionWidget);
    }

    public function install()
    {
        // import the sql
        $this->importSQL(dirname(__FILE__) . '/Data/install.sql');

        // install the module in the database
        $this->addModule('Compression');

        // install the locale, this is set here beceause we need the module for this
        $this->importLocale(dirname(__FILE__) . '/Data/locale.xml');

        // module rights
        $this->setModuleRights(1, 'Compression');

        // action rights
        $this->setActionRights(1, 'Compression', 'Settings');

        // settings navigation
        $navigationSettingsId = $this->setNavigation(null, 'Settings');
        $navigationModulesId = $this->setNavigation($navigationSettingsId, 'Modules');
        $this->setNavigation($navigationModulesId, 'Compression', 'compression/settings');

        // install dashboardwidget
        $this->insertWidget();
    }
}
