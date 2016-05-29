<?php

namespace Backend\Modules\Compression\Ajax;

use Backend\Core\Engine\Base\AjaxAction as BackendBaseAJAXAction;
use Backend\Modules\Compression\Engine\Model as BackendCompressionModel;
use SpoonFilter;

/**
 * This is the console ajax action that will erase the cache file
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class ConsoleErase extends BackendBaseAJAXAction
{
    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();

        $overwrite = (bool) SpoonFilter::getPostValue('overwrite', null, '');
        if ($overwrite) {
            BackendCompressionModel::writeToCacheFile('');
        }

        $this->output(self::OK);
    }
}
