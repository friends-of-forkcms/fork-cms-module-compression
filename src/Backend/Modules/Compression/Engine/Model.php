<?php

namespace Backend\Modules\Compression\Engine;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Language;
use Backend\Core\Engine\Language as BL;

/**
 * In this file we store all generic functions that we will be using in the Compression module
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class Model
{
	/**
	 * Fetches all folders
	 *
	 * @return array
	 */
	public static function getAllFolders()
	{
		return (array) BackendModel::get('database')->getRecords(
			'SELECT i.id, i.title, i.path, UNIX_TIMESTAMP(i.created_on) AS created_on
             FROM compression_folders AS i'
		);
	}

	public static function insertFolders($folders)
	{
		// get db
		$db = BackendModel::getContainer()->get('database');

		// delete all records first
		$db->delete('compression_folders', null);

		// Insert folders
		$db->insert('compression_folders', $folders);
	}
}