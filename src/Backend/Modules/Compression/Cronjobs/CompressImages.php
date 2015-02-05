<?php

namespace Backend\Modules\Compression\Cronjobs;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Symfony\Component\Finder\Finder;
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
    private $apiKey;

    private $filesPath;

    private $images = array();


    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();
        $this->filesPath = FRONTEND_FILES_PATH;

        // Get data from db
        $this->getData();

        // Compress images
        $this->compressImages();
    }

    private function getData() {
        // Get api key
        $this->apiKey = BackendModel::getModuleSetting($this->getModule(), 'api_key', null);

        if (!isset($this->apiKey)) {
            throw new \SpoonException('API Key is missing');
        }

        // Get data from db
        $folders = BackendCompressionModel::getAllFolders();
        $finder = new Finder();

        foreach($folders as $folder) {
            $iterator = $finder
                ->files()
                ->name('/\.(jpg|jpeg|png)$/i')
                ->in($this->filesPath . $folder['path']);

            foreach($iterator as $file) {
                $this->images[] = array(
                    'filename' => $file->getFilename(),
                    'full_path' => $file->getRealpath(),
                    'file_size_original' => $file->getSize()
                );
            }
        }

    }

    private function compressImages() {

        print("Compressing " . count($this->images) . " images..." . "<br />\r\n");

        foreach($this->images as $image) {

            $request = curl_init();
            curl_setopt_array($request, array(
                CURLOPT_URL => "https://api.tinypng.com/shrink",
                CURLOPT_USERPWD => "api:" . $this->apiKey,
                CURLOPT_POSTFIELDS => file_get_contents($image['full_path']),
                CURLOPT_BINARYTRANSFER => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                /* Uncomment below if you have trouble validating our SSL certificate.
				   Download cacert.pem from: http://curl.haxx.se/ca/cacert.pem */
                // CURLOPT_CAINFO => __DIR__ . "/cacert.pem",
                CURLOPT_SSL_VERIFYPEER => true
            ));

            $response = curl_exec($request);
            if (curl_getinfo($request, CURLINFO_HTTP_CODE) === 201) {
                /* Compression was successful, retrieve output from Location header. */
                $headers = substr($response, 0, curl_getinfo($request, CURLINFO_HEADER_SIZE));
                foreach (explode("\r\n", $headers) as $header) {
                    if (substr($header, 0, 10) === "Location: ") {
                        $request = curl_init();
                        curl_setopt_array($request, array(
                            CURLOPT_URL => substr($header, 10),
                            CURLOPT_RETURNTRANSFER => true,
                            /* Uncomment below if you have trouble validating our SSL certificate. */
                            // CURLOPT_CAINFO => __DIR__ . "/cacert.pem",
                            CURLOPT_SSL_VERIFYPEER => true
                        ));
                        file_put_contents($image['full_path'], curl_exec($request));
                    }
                }

                $imageInfo = new \SplFileInfo($image['full_path']);
                $image['file_size_compressed'] = $imageInfo->getSize();
                $image['saved_bytes'] = (int) $image['file_size_original'] - (int) $image['file_size_compressed'];

                print("Compression succesful for image " . $image['filename'] . ". Saved " . number_format($image['saved_bytes'] / 1024, 2) . ' KB' . " bytes" . "<br />\r\n");
            } else {
                // Something went wrong
                print(curl_error($request));
                print("Compression failed for image " . $image . "<br />\r\n");
            }

        }
    }
}
