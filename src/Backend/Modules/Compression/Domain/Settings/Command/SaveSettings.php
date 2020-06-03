<?php
declare(strict_types=1);

namespace Backend\Modules\Compression\Domain\Settings\Command;

use Common\ModulesSettings;
use Symfony\Component\Validator\Constraints as Assert;

final class SaveSettings
{
    /**
     * @var string
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $apiKey;

    public function __construct(ModulesSettings $modulesSettings)
    {
        $settings = $modulesSettings->getForModule('Compression');
        $this->apiKey = $settings['api_key'] ?? null;
    }
}
