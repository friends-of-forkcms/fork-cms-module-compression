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
    private function getCompressionParameters() {
        $remove = $this->getParameter('remove');

        // something has to be removed before proceeding
        if (!empty($remove)) {
            // the session token has te be removed
            if ($remove == 'api_key') {
                BackendModel::setModuleSetting($this->getModule(), 'api_key', null );
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
        $this->frmCompressionSettings = new BackendForm('compressionSettings');
        $this->frmCompressionSettings->addHidden('dummy_folders');
        $this->directoryTreeHtml = $this->BuildDirectoryTreeHtml(FRONTEND_FILES_PATH);

        // use POST values to rebuild the folders
        $this->folders = array();
        if($this->frmCompressionSettings->isSubmitted()) {
            if(isset($_POST['folders']) && is_array($_POST['folders'])) {
                foreach($_POST['folders'] as $folder) {
                    $this->folders[] = array(
                        'path' => '/' . (string) $folder,
                        'created_on' => BackendModel::getUTCDate()
                    );
                }
            }
        }
        $this->tpl->assign('folders', json_encode($this->folders));
    }


    /**
     * Build a directory tree in html list
     * Based on: https://stackoverflow.com/questions/24121723/multidimensional-directory-list-with-recursive-iterator
     *
     * @param string $folder_path
     * @param int $depth
     *
     * @return string Html directory tree
     */
    public function BuildDirectoryTreeHtml($folder_path = '', $depth = 0)
    {
        $iterator = new \IteratorIterator(new \DirectoryIterator($folder_path));

        $r = "<ul>";

        foreach ($iterator as $splFileInfo) {

            if ($splFileInfo->isDot()) {
                continue;
            }

            // is we have a directory, try and get its children
            if ($splFileInfo->isDir()) {
                $r .= "<li>";

                $r .= $splFileInfo->getFilename();

                $nodes = $this->BuildDirectoryTreeHtml($splFileInfo->getPathname(), $depth + 1);

                // only add the nodes if we have some
                if (!empty($nodes)) {
                    $r .= $nodes;
                }

                $r .= "</li>";
            }

        }

        $r .= "</ul>";

        return $r;
    }

    /**
     * Validates the tracking url form.
     */
    private function validateCompressionSettingsForm()
    {
        if ($this->frmCompressionSettings->isSubmitted()) {
            if ($this->frmCompressionSettings->isCorrect()) {

                $this->frmCompressionSettings->cleanupFields();

                // validate fields
                if($this->frmCompressionSettings->isCorrect()) {

                    // insert the folders
                    BackendCompressionModel::insertFolders($this->folders);
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

        $this->header->addJS('jstree.min.js', $this->getModule(), false, false);
        $this->header->addCSS('jstree/style.min.css', $this->getModule(), false, false);

        if (!isset($this->apiKey)) {
            $this->tpl->assign('NoApiKey', true);
            $this->tpl->assign('Wizard', true);

            // create form
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
            // show the linked account
            $this->tpl->assign('EverythingIsPresent', true);

            $this->loadCompressionSettingsForm();
            $this->tpl->assign('directoryTree', $this->directoryTreeHtml);
            $this->validateCompressionSettingsForm();

            $this->frmCompressionSettings->parse($this->tpl);
        }
    }
}
