<?php
declare(strict_types=1);

namespace Backend\Modules\Compression\Actions;

use Backend\Core\Engine\Base\ActionIndex;
use Backend\Core\Engine\Model;
use Backend\Modules\Compression\Domain\Settings\Command\SaveSettings;
use Backend\Modules\Compression\Domain\Settings\Event\SettingsSavedEvent;
use Backend\Modules\Compression\Domain\Settings\SettingsType;
use Backend\Modules\Compression\Exception\ValidateResponseErrorException;
use Backend\Modules\Compression\Http\TinyPngApiClient;
use Symfony\Component\Form\Form;

final class Settings extends ActionIndex
{
    public function execute(): void
    {
        parent::execute();

        $form = $this->getForm();

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());

            if ($this->get('fork.settings')->get($this->getModule(), 'api_key')) {
                try {
                    $client = TinyPngApiClient::createFromModuleSettings($this->get('fork.settings'));
                    $this->template->assign('monthlyCompressionCount', $client->getMonthlyCompressionCount());
                } catch (ValidateResponseErrorException $e) {
                    $this->get('fork.settings')->delete($this->getModule(), 'api_key');
                    $this->redirect(
                        Model::createUrlForAction('Settings', $this->getModule(), null, ['error' => 'invalid-api-key'])
                    );
                }
            }

            $this->parse();
            $this->display();

            return;
        }

        $settings = $this->saveSettings($form);
        $this->get('event_dispatcher')->dispatch(SettingsSavedEvent::EVENT_NAME, new SettingsSavedEvent($settings));

        $this->redirect(Model::createUrlForAction('Ping', $this->getModule(), null, ['report' => 'saved']));
    }

    private function getForm(): Form
    {
        $form = $this->createForm(
            SettingsType::class,
            new SaveSettings($this->get('fork.settings'))
        );

        $form->handleRequest($this->getRequest());
        return $form;
    }

    private function saveSettings(Form $form): SaveSettings
    {
        /** @var SaveSettings $settings */
        $settings = $form->getData();
        $this->get('command_bus')->handle($settings);

        return $settings;
    }
}
