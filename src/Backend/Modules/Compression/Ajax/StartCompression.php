<?php

namespace Backend\Modules\Compression\Ajax;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\AjaxAction as BackendBaseAJAXAction;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Compression\Engine\Model as BackendCompressionModel;
use Symfony\Component\Finder\Finder;

/**
 * This is the console ajax action
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class StartCompression extends BackendBaseAJAXAction
{
    /**
     * API key needed by the API.
     *
     * @var string
     */
    private $apiKey;

    /**
     * All of the images that are inside the selected folders
     *
     * @var array The array containing all the images
     */
    private $images;

    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();

        // Get data from db
        $this->getData();

        // Compress each image from each folder
        $output = 'Compressing ' . count($this->images) . ' images...' . "\r\n";
        BackendCompressionModel::writeToCacheFile($output, true);

        foreach ($this->images as $image) {
            BackendCompressionModel::compressImage($this->apiKey, $image);
        }

        BackendCompressionModel::writeToCacheFile("...Done!");

        $this->output(self::OK);
    }

    /**
     * Get the api key and the array containing all the images to be compressed.
     *
     * @throws \SpoonException
     */
    private function getData()
    {
        // Get api key
        $this->apiKey = BackendModel::getModuleSetting($this->getModule(), 'api_key', null);

        if (!isset($this->apiKey)) {
            throw new \SpoonException('API Key is missing');
        }

        // Get data from db
        $folders = BackendCompressionModel::getAllFolders();

        // Select all the jpg & png images inside the folder and add them to the images array
        foreach ($folders as $folder) {
            $finder = new Finder();
            $iterator = $finder
                ->files()
                ->name('/\.(jpg|jpeg|png)$/i')
                ->in($folder['path']);

            foreach ($iterator as $file) {
                $this->images[] = array(
                    'filename' => $file->getFilename(),
                    'full_path' => $file->getRealpath(),
                    'file_size_original' => $file->getSize()
                );
            }
        }
    }
}
