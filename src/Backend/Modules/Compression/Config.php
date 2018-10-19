<?php

namespace Backend\Modules\Compression;

use Backend\Core\Engine\Base\Config as BaseConfig;

/**
 * This is the configuration-object for the Compressor module
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
final class Config extends BaseConfig
{
    /**
     * The default action
     *
     * @var string
     */
    protected $defaultAction = 'Settings';

    /**
     * The disabled actions
     *
     * @var array
     */
    protected $disabledActions = array();
}
