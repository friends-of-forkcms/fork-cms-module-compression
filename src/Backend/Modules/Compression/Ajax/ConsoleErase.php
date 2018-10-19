<?php

namespace Backend\Modules\Compression\Ajax;

use Backend\Core\Engine\Base\AjaxAction as BackendBaseAJAXAction;
use Backend\Modules\Compression\Engine\Model as BackendCompressionModel;
use Symfony\Component\HttpFoundation\Response;

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
    public function execute(): void
    {
        parent::execute();

        $overwrite = $this->getRequest()->request->get('overwrite', '');
        if ($overwrite) {
            BackendCompressionModel::writeToCacheFile('');
        }

        // output
        $this->output(Response::HTTP_OK);
    }
}
