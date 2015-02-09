<?php

namespace Backend\Modules\Compression\Cronjobs;

use Backend\Modules\Compression\Engine\TinyPNGApi;
use Backend\Core\Engine\Base\Cronjob as BackendBaseCronjob;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Compression\Engine\Model as BackendCompressionModel;

/**
 * This cronjob will compress the images inside the configured folders
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class CompressImages extends BackendBaseCronjob
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
    private $images = array();


    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();

        // Get api key
        $this->apiKey = BackendModel::getModuleSetting($this->getModule(), 'api_key', null);

        // Get uncompressed images list
        $this->images = BackendCompressionModel::getImagesFromFolders();

        if (!empty($this->images)) {
            // Compress each image from each folder
            $output = 'Compressing ' . count($this->images) . ' images...' . "<br />\r\n";
            BackendCompressionModel::writeToCacheFile($output, true);

            foreach ($this->images as $image) {
                $tinyPNGApi = new TinyPNGApi($this->apiKey);

                // Shrink the image and check if succesful
                if ($tinyPNGApi->shrink($image['full_path'])) {
                    // Check if the file was successfully downloaded.
                    if ($tinyPNGApi->download($image['full_path'])) {
                        $output = 'Compression succesful for image ' . $image['filename'] . '. Saved ' . number_format($tinyPNGApi->getSavingSize() / 1024, 2) . ' KB' . ' bytes. (' . $tinyPNGApi->getSavingPercentage() . '%)';
                        BackendCompressionModel::writeToCacheFile($output);

                        // Save to db
                        $imageInfo = array(
                            'filename' => $image['filename'],
                            'path' => $image['full_path'],
                            'original_size' => $tinyPNGApi->getInputSize(),
                            'compressed_size' => $tinyPNGApi->getOutputSize(),
                            'saved_bytes' => $tinyPNGApi->getSavingSize(),
                            'saved_percentage' => $tinyPNGApi->getSavingPercentage(),
                            'checksum_hash' => sha1_file($image['full_path']),
                            'compressed_on' => BackendModel::getUTCDate()
                        );
                        BackendCompressionModel::insertImageHistory($imageInfo, $image['file_compressed_before']);
                    }
                } else {
                    BackendCompressionModel::writeToCacheFile($tinyPNGApi->getErrorMessage());
                }
            }

            BackendCompressionModel::writeToCacheFile("...Done!");
        } else {
            BackendCompressionModel::writeToCacheFile('There are no images that can be compressed.', true);
        }

        // Print the output for debug purposes
        $output = BackendCompressionModel::readCacheFile();
        print($output);
    }
}
