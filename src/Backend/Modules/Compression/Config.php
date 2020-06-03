<?php
declare(strict_types=1);

namespace Backend\Modules\Compression;

use Backend\Core\Engine\Base\Config as BackendBaseConfig;

final class Config extends BackendBaseConfig
{
    /**
     * The default action
     *
     * @var string
     */
    protected $defaultAction = 'Settings';
}
