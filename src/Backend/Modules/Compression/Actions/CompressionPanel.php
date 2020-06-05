<?php
declare(strict_types=1);

namespace Backend\Modules\Compression\Actions;

use Backend\Core\Engine\Base\ActionIndex;
use Backend\Core\Engine\Model;
use Backend\Modules\Compression\Domain\CompressionSetting\Command\UpdateCompressionSettings;
use Backend\Modules\Compression\Domain\CompressionSetting\CompressionSetting;
use Backend\Modules\Compression\Domain\CompressionSetting\CompressionSettingRepository;
use Backend\Modules\Compression\Domain\CompressionSetting\CompressionSettingsType;
use Backend\Modules\Compression\Domain\CompressionSetting\Event\CompressionSettingsUpdated;
use Backend\Modules\Compression\Domain\CompressionSetting\Helpers\Helper;
use Symfony\Component\Form\Form;

/**
 * Class CompressionPanel
 * @package Backend\Modules\Compression\Actions
 */
final class CompressionPanel extends ActionIndex
{
    public function execute(): void
    {
        parent::execute();

        // Check if we have an API key configured already
        if (!$this->get('fork.settings')->get($this->getModule(), 'api_key')) {
            $this->redirect(Model::createUrlForAction('Settings', $this->getModule(), null));
        }

        $form = $this->getForm();

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());

            $this->parse();
            $this->display();

            return;
        }

        $updateCompressionSettings = $this->updateCompressionSettings($form);

        $this->get('event_dispatcher')->dispatch(
            CompressionSettingsUpdated::EVENT_NAME,
            new CompressionSettingsUpdated()
        );

        $this->redirect(
            Model::createUrlForAction(
                'CompressionPanel',
                $this->getModule(),
                null,
                [
                    'report' => 'saved',
                ]
            )
        );
    }

    protected function parse(): void
    {
        parent::parse();

        // Build a directory tree from the Files directory
        $directoryTreeHtml = Helper::BuildDirectoryTreeJson(FRONTEND_FILES_PATH, 0, []);
        $this->template->assign('directoryTree', $directoryTreeHtml);

        /** @var CompressionSettingRepository $settingsRepository */
        $settingsRepository = $this->get('compression.repository.compression_setting');
        $selectedFolderPaths = array_map(function (CompressionSetting $setting) {
            return $setting->getPath();
        }, $settingsRepository->findAll());
        $this->template->assign('selectedFolders', $selectedFolderPaths);
    }

    private function getForm(): Form
    {
        $form = $this->createForm(
            CompressionSettingsType::class,
            new UpdateCompressionSettings()
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function updateCompressionSettings(Form $form): UpdateCompressionSettings
    {
        /** @var UpdateCompressionSettings $settings */
        $settings = $form->getData();

        // The command bus will handle the saving of the settings in the database.
        $this->get('command_bus')->handle($settings);

        return $settings;
    }
}
