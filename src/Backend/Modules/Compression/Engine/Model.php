<?php

namespace Backend\Modules\Compression\Engine;

use Backend\Core\Engine\Model as BackendModel;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * In this file we store all generic functions that we will be using in the Compression module
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class Model
{
    /**
     * Fetches all selected folders
     *
     * @return array Selected folders and their path
     */
    public static function getAllFolders()
    {
        return (array) BackendModel::get('database')->getRecords(
            'SELECT i.id, i.title, i.path, UNIX_TIMESTAMP(i.created_on) AS created_on
             FROM compression_folders AS i'
        );
    }

    /**
     * Insert the checked folders and their path
     *
     * @param $folders Array folders array
     */
    public static function insertFolders($folders)
    {
        // get db
        $db = BackendModel::getContainer()->get('database');

        // delete all records first
        $db->delete('compression_folders', null);

        // Insert folders
        $db->insert('compression_folders', $folders);
    }

    /**
     * Write a message to the compression cache output file.
     *
     * @param $data String The message
     * @param bool $overwrite Whether to overwrite the whole file with the new data, or not.
     */
    public static function writeToCacheFile($data, $overwrite = false)
    {
        // store
        $fs = new Filesystem();
        $output = "";

        if ($overwrite) {
            $output = $data;
        } else {
            $output = self::readCacheFile();
            $output .= $data . "\r\n";
        }

        $fs->dumpFile(
            BACKEND_CACHE_PATH . '/Compression/output.log',
            $output
        );
    }


    /**
     * Remove all cache files
     */
    public static function removeCacheFiles()
    {
        $finder = new Finder();
        $fs = new Filesystem();
        foreach ($finder->files()->in(BACKEND_CACHE_PATH . '/Compression') as $file) {
            $fs->remove($file->getRealPath());
        }
    }

    /**
     * Read the compression output cache file
     *
     * @return string The contents of the cache file
     */
    public static function readCacheFile()
    {
        $fs = new Filesystem();
        $file = BACKEND_CACHE_PATH . '/Compression/output.log';
        $cacheFile = "";

        if ($fs->exists($file)) {
            $cacheFile = file_get_contents(BACKEND_CACHE_PATH . '/Compression/output.log');
        }

        return $cacheFile;
    }

    /**
     * Compress an image. Send them to the TinyPNG api and save it back.
     */
    public static function compressImage($apiKey, $image)
    {
        $request = curl_init();
        curl_setopt_array($request, array(
            CURLOPT_URL => 'https://api.tinypng.com/shrink',
            CURLOPT_USERPWD => 'api:' . $apiKey,
            CURLOPT_POSTFIELDS => file_get_contents($image['full_path']),
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            /* Uncomment below if you have trouble validating our SSL certificate.
               Download cacert.pem from: http://curl.haxx.se/ca/cacert.pem */
            CURLOPT_CAINFO => __DIR__ . "/cacert.pem",
            CURLOPT_SSL_VERIFYPEER => true
        ));

        $response = curl_exec($request);
        if (curl_getinfo($request, CURLINFO_HTTP_CODE) === 201) {
            /* Compression was successful, retrieve output from Location header. */
            $headers = substr($response, 0, curl_getinfo($request, CURLINFO_HEADER_SIZE));
            foreach (explode("\r\n", $headers) as $header) {
                if (substr($header, 0, 10) === 'Location: ') {
                    $request = curl_init();
                    curl_setopt_array($request, array(
                        CURLOPT_URL => substr($header, 10),
                        CURLOPT_RETURNTRANSFER => true,
                        /* Uncomment below if you have trouble validating our SSL certificate. */
                        CURLOPT_CAINFO => __DIR__ . "/cacert.pem",
                        CURLOPT_SSL_VERIFYPEER => true
                    ));
                    file_put_contents($image['full_path'], curl_exec($request));
                }
            }

            // Get statistics
            $imageInfo = new \SplFileInfo($image['full_path']);
            $image['file_size_compressed'] = $imageInfo->getSize();
            $image['saved_bytes'] = (int) $image['file_size_original'] - (int) $image['file_size_compressed'];
            $image['saved_percentage'] = (int) ($image['saved_bytes'] / $image['file_size_original'] * 100);
            $image['path'] = str_replace($image['filename'], '', str_replace(str_replace('/app/..', '', FRONTEND_FILES_PATH), '', $image['full_path']));

            $output = 'Compression succesful for image ' . $image['filename'] . '. Saved ' . number_format($image['saved_bytes'] / 1024, 2) . ' KB' . ' bytes. (' . $image['saved_percentage'] . '%)';
            self::writeToCacheFile($output);

            // get db
            $db = BackendModel::getContainer()->get('database');
            $db->insert('compression_history', array(
                'filename' => $image['filename'],
                'folder_path' => $image['path'],
                'original_size' => $image['file_size_original'],
                'compressed_size' => $image['file_size_compressed'],
                'saved_bytes' => $image['saved_bytes'],
                'saved_percentage' => $image['saved_percentage'],
                'compressed_on' => BackendModel::getUTCDate()
            ));
        } else {
            // Something went wrong!
            self::writeToCacheFile(curl_error($request));
            self::writeToCacheFile('Compression failed for image ' . $image . "\r\n");
        }
    }
}
