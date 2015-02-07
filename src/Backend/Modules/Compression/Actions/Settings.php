<?php

namespace Backend\Modules\Compression\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\Language as BL;
use Backend\Modules\Compression\Engine\Model as BackendCompressionModel;
use Symfony\Component\Finder\Finder;

/**
 * This is the settings-action, it will display a form to set general compression settings
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class Settings extends BackendBaseActionEdit
{
    /**
     * API key needed by the API.
     *
     * @var string
     */
    private $apiKey;

    /**
     * The forms used on this page
     *
     * @var BackendForm
     */
    private $frmApiKey;
    private $frmCompressionSettings;

    /**
     * The saved directories from the database
     *
     * @var Array
     */
    private $savedDirectories;

    /**
     * The generated directory tree in html
     *
     * @var String
     */
    private $directoryTreeHtml;

    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();
        $this->getCompressionParameters();
        $this->parse();
        $this->display();
    }

    /**
     * Get the api key
     */
    private function getCompressionParameters()
    {
        $remove = $this->getParameter('remove');

        // something has to be removed before proceeding
        if (!empty($remove)) {
            // the session token has te be removed
            if ($remove == 'api_key') {
                BackendModel::setModuleSetting($this->getModule(), 'api_key', null);
            }

            // account was deleted, so redirect
            $this->redirect(BackendModel::createURLForAction('Settings') . '&report=deleted');
        }

        $this->apiKey = BackendModel::getModuleSetting($this->getModule(), 'api_key', null);
    }

    /**
     * Load settings form
     */
    private function loadCompressionSettingsForm()
    {
        // create compression folder form
        $this->frmCompressionSettings = new BackendForm('compressionSettings');
        $this->frmCompressionSettings->addHidden('dummy_folders');

        // get saved folders
        $this->savedDirectories = BackendCompressionModel::getAllFolders();

        // build directory tree
        $this->directoryTreeHtml = $this->BuildDirectoryTreeHtml(FRONTEND_FILES_PATH);

        // use POST values to rebuild the folders
        $this->folders = array();
        if ($this->frmCompressionSettings->isSubmitted()) {
            if (isset($_POST['folders']) && is_array($_POST['folders'])) {
                foreach ($_POST['folders'] as $folder) {
                    $this->folders[] = array(
                        'path' => (string) $folder,
                        'created_on' => BackendModel::getUTCDate()
                    );
                }
            }
        }
        $this->tpl->assign('folders', json_encode($this->folders));
    }


    /**
     * Build a directory tree in html list
     *
     * @param string $folder_path The root folder path
     * @param int $depth The recursive depth
     *
     * @return string A directory tree in HTML
     */
    public function BuildDirectoryTreeHtml($folder_path = '', $depth = 0)
    {
        $iterator = new \IteratorIterator(new \DirectoryIterator($folder_path));

        $r = '<ul>';

        foreach ($iterator as $splFileInfo) {
            if ($splFileInfo->isDot()) {
                continue;
            }

            // if we have a directory, try and get its children
            if ($splFileInfo->isDir()) {
                // Compare the path of this directory with the path of the directories saved in the database. Check the folder if they match.
                $checkFolder = false;
                $currentFolderPath = $splFileInfo->getRealPath();

                foreach ($this->savedDirectories as $dbDirectory) {
                    if ($dbDirectory['path'] == $currentFolderPath) {
                        $checkFolder = true;
                        break;
                    }
                }

                if ($checkFolder) {
                    $r .= '<li class="checked" data-path="' . $currentFolderPath . '">';
                } else {
                    $r .= '<li data-path="' . $currentFolderPath . '">';
                }

                // Add the filename to the li element
                $r .= $splFileInfo->getFilename();

                // Add the folder count
                $finder = new Finder();
                $folderCount = $finder
                    ->files()
                    ->name('/\.(jpg|jpeg|png)$/i')
                    ->in($splFileInfo->getRealPath())
                    ->count();
                $r .= ' (' . $folderCount . ')';

                // get the nodes
                $nodes = $this->BuildDirectoryTreeHtml($splFileInfo->getPathname(), $depth + 1);

                // only add the nodes if we have some
                if (!empty($nodes)) {
                    $r .= $nodes;
                }

                $r .= '</li>';
            }
        }

        $r .= '</ul>';

        return $r;
    }

    /**
     * Validates the compression settings form.
     */
    private function validateCompressionSettingsForm()
    {
        if ($this->frmCompressionSettings->isSubmitted()) {
            if ($this->frmCompressionSettings->isCorrect()) {
                $this->frmCompressionSettings->cleanupFields();

                // validate fields
                if ($this->frmCompressionSettings->isCorrect()) {
                    if (!empty($this->folders)) {
                        // insert the folders
                        BackendCompressionModel::insertFolders($this->folders);
                    }
                }

                BackendModel::triggerEvent($this->getModule(), 'after_saved_settings');
                $this->redirect(BackendModel::createURLForAction('Settings') . '&report=saved');
            }
        }
    }

    /**
     * Parse the form
     */
    protected function parse()
    {
        parent::parse();

        // Add jsTree plugin
        $this->header->addJS('jstree.min.js', $this->getModule(), false, false);
        $this->header->addCSS('jstree/style.min.css', $this->getModule(), false, false);

        // Show the API key form if we don't have one set
        if (!isset($this->apiKey)) {
            $this->tpl->assign('NoApiKey', true);
            $this->tpl->assign('Wizard', true);

            // create api key form
            $this->frmApiKey = new BackendForm('apiKey');
            $this->frmApiKey->addText('key', $this->apiKey);

            if ($this->frmApiKey->isSubmitted()) {
                $this->frmApiKey->getField('key')->isFilled(BL::err('FieldIsRequired'));

                if ($this->frmApiKey->isCorrect()) {
                    BackendModel::setModuleSetting(
                        $this->getModule(),
                        'api_key',
                        $this->frmApiKey->getField('key')->getValue()
                    );
                    $this->redirect(BackendModel::createURLForAction('Settings') . '&report=saved');
                }
            }

            $this->frmApiKey->parse($this->tpl);
        } else {
            // show the settings form
            $this->tpl->assign('EverythingIsPresent', true);

            $this->loadCompressionSettingsForm();
            $this->tpl->assign('directoryTree', $this->directoryTreeHtml);
            $this->validateCompressionSettingsForm();

            $this->frmCompressionSettings->parse($this->tpl);
        }
    }
}
