<?php

namespace Backend\Modules\Compression\Ajax;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\AjaxAction as BackendBaseAJAXAction;
use Backend\Modules\Compression\Engine\Model as BackendCompressionModel;

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

		$overwrite = (boolean) \SpoonFilter::getPostValue('overwrite', null, '');
		if($overwrite) BackendCompressionModel::writeToCacheFile("");

		$this->output(self::OK);
	}
}
