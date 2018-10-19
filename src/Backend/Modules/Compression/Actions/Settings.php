<?php

namespace Backend\Modules\Compression\Actions;

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Language\Language as BL;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Modules\Compression\Engine\Helper;
use Backend\Modules\Compression\Engine\Model as BackendCompressionModel;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * The Compression settings are able to define a TinyPNG api key and select folders to compress.
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class Settings extends BackendBaseActionEdit
{
    /**
     * TinyPNG API key.
     *
     * @var string
     */
    private $apiKey;

    /**
     * Api key form
     *
     * @var BackendForm
     */
    private $frmApiKey;

    /**
     * Compression module settingsform
     *
     * @var BackendForm
     */
    private $frmCompressionSettings;

    /**
     * The saved directories from the database
     *
     * @var array
     */
    private $savedDirectories;

    /**
     * @var array
     */
    private $folders;

    /**
     * The generated directory tree in html
     *
     * @var String
     */
    private $directoryTreeHtml;

    /**
     * Settings constructor.
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        parent::__construct($kernel);
        $this->savedDirectories = [];
        $this->folders = [];
    }

    /**
     * Execute the action
     */
    public function execute(): void
    {
        parent::execute();
        $this->getCompressionParameters();
        $this->parse();
        $this->display();
    }

    /**
     * Get the api key
     */
    private function getCompressionParameters(): void
    {
        $removeAction = $this->getRequest()->query->get('remove');

        // We need to remove the api key
        if (!empty($removeAction)) {
            // the session token has te be removed
            if ($removeAction == 'api_key') {
                $this->get('fork.settings')->set($this->getModule(), 'api_key', null);
            }

            // TinyPNG account was unlinked, so redirect back.
            $this->redirect(BackendModel::createURLForAction('Settings') . '&report=deleted');
        }

        $this->apiKey = $this->get('fork.settings')->get($this->getModule(), 'api_key', null);
    }

    /**
     * Load the compression settings form
     */
    private function loadCompressionSettingsForm(): void
    {
        // Create compression folder form
        $this->frmCompressionSettings = new BackendForm('compression');
        $this->frmCompressionSettings->addHidden('dummy_folders');

        // Get saved folders from the db
        $this->savedDirectories = BackendCompressionModel::getAllFolders();

        // Build directory tree
        $this->directoryTreeHtml = Helper::BuildDirectoryTreeHtml(FRONTEND_FILES_PATH, 0, $this->savedDirectories);

        // Use POST values to rebuild the folders
        $this->folders = array();
        if ($this->frmCompressionSettings->isSubmitted()) {
            if (isset($_POST['folders']) && is_array($_POST['folders'])) {
                foreach ($_POST['folders'] as $folder) {
                    $this->folders[] = array(
                        'path' => (string)$folder,
                        'created_on' => BackendModel::getUTCDate()
                    );
                }
            }
        }
    }

    /**
     * Validates the compression settings form.
     */
    private function validateCompressionSettingsForm(): void
    {
        if ($this->frmCompressionSettings->isSubmitted()) {
            if ($this->frmCompressionSettings->isCorrect()) {
                $this->frmCompressionSettings->cleanupFields();

                // Validate fields
                if ($this->frmCompressionSettings->isCorrect()) {
                    if (!empty($this->folders)) {
                        // Insert the folders
                        BackendCompressionModel::insertFolders($this->folders);
                    }
                }
                $this->redirect(BackendModel::createURLForAction('Settings') . '&report=saved');
            }
        }
    }

    /**
     * Parse the form
     */
    protected function parse(): void
    {
        parent::parse();

        // Add jsTree plugin
        $this->header->addJS('jstree.min.js', $this->getModule(), false, false);
        $this->header->addCSS('jstree/style.min.css', $this->getModule(), false, false);

        // Create api key form
        if (!isset($this->apiKey)) {
            $this->frmApiKey = new BackendForm('settings');
            $this->frmApiKey->addText('key', $this->apiKey)->setAttribute('placeholder', BL::lbl('YourApiKey'));

            if ($this->frmApiKey->isSubmitted()) {
                $this->frmApiKey->getField('key')->isFilled(BL::err('FieldIsRequired'));

                if ($this->frmApiKey->isCorrect()) {
                    $apikeyFieldValue = $this->frmApiKey->getField('key')->getValue();
                    $this->get('fork.settings')->set($this->getModule(), 'api_key', $apikeyFieldValue);
                    $this->redirect(BackendModel::createURLForAction('Settings') . '&report=saved');
                }
            }

            // Parse the form into the template
            $this->frmApiKey->parse($this->template);
        }

        // Show the actual settings form
        if (isset($this->apiKey)) {
            $this->loadCompressionSettingsForm();
            $this->template->assign('directoryTree', $this->directoryTreeHtml);
            $this->validateCompressionSettingsForm();

            // Parse the form into the template
            $this->frmCompressionSettings->parse($this->template);
        }

        // Show the API key form if we don't have one set
        $this->template->assign('apiKey', $this->apiKey);
        $this->template->assign('folders', array_merge($this->savedDirectories, $this->folders));
    }
}
