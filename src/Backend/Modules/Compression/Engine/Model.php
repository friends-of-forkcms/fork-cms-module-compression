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
     * @param array $folders
     */
    public static function insertFolders($folders)
    {
        // get db
        $db = BackendModel::getContainer()->get('database');

        // delete all records first
        $db->delete('compression_folders', null);

        // Remove duplicate paths
        $foldersWithoutDup = array_map("unserialize", array_unique(array_map("serialize", $folders)));

        // Insert folders
        $db->insert('compression_folders', $foldersWithoutDup);
    }

    /**
     * Insert information about the compressed image
     *
     * @param array $imageInfo The info about the compressed image
     * @param bool $fileCompressedBefore
     */
    public static function insertImageHistory($imageInfo, $fileCompressedBefore)
    {
        $db = BackendModel::getContainer()->get('database');

        // Update status of previous record(s)
        if ($fileCompressedBefore) {
            $db->update('compression_history', array('status' => 'archived'), 'path = ?', $imageInfo['path']);
        }

        $db->insert('compression_history', $imageInfo);
    }

    /**
     * Find an image record in the history table
     *
     * @param string $image_path The image path
     * @return array
     */
    public static function getImageHistory($image_path)
    {
        $db = BackendModel::getContainer()->get('database');
        $record = $db->getRecord(
            'SELECT i.id, i.path, i.compressed_size, i.checksum_hash
            FROM compression_history AS i
            WHERE i.path = ? AND i.status = ?',
            array($image_path, 'active')
        );
        return $record;
    }

    /**
     * Write a message to the compression cache output file.
     *
     * @param $data String The message
     * @param bool $overwrite Whether to overwrite the whole file with the new data, or not.
     */
    public static function writeToCacheFile($data, $overwrite = false)
    {
        $fs = new Filesystem();

        if ($overwrite) {
            $output = $data;
        } else {
            $output = self::readCacheFile();
            $output .= $data . "\r\n";
        }

        $fs->dumpFile(BACKEND_CACHE_PATH . '/Compression/output.log', $output);
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
     * Get the array of uncompressed images from all the watched folders.
     *
     * @return array List of uncompressed folders from selected folders
     */
    public static function getImagesFromFolders()
    {
        // The images array
        $images = array();

        // Get data from db
        $folders = self::getAllFolders();

        // Select all the jpg & png images inside the folder and add them to the images array
        foreach ($folders as $folder) {
            $finder = new Finder();
            $iterator = $finder
                ->files()
                ->name('/\.(jpg|jpeg|png)$/i')
                ->depth('== 0')
                ->in($folder['path']);

            foreach ($iterator as $file) {
                $addImage = false;
                $compressedBefore = false;

                // Find an existing record in the image history records
                $imageRecord = self::getImageHistory($file->getRealPath());

                // Check if the file has been compressed before and thus exists in the db
                if ($imageRecord) {
                    // Check if checksum hash is different (file has changed)
                    if (sha1_file($file->getRealPath()) != $imageRecord['checksum_hash']) {
                        $addImage = true;
                    }

                    $compressedBefore = true;
                } else {
                    $addImage = true;
                }

                if ($addImage) {
                    $images[] = array(
                        'filename' => $file->getFilename(),
                        'full_path' => $file->getRealpath(),
                        'file_size_original' => $file->getSize(),
                        'file_compressed_before' => $compressedBefore
                    );
                }
            }
        }

        return $images;
    }

    /**
     * Get some statistics about the compression, like the Weissman score™
     *
     * @return array Compression statistics
     */
    public static function getStatistics()
    {
        $db = BackendModel::getContainer()->get('database');

        return $db->getRecord(
            'SELECT 
                COUNT(i.id) AS total_compressed, 
                SUM(i.saved_bytes) AS saved_bytes,
                concat(round(( 100 - (SUM(compressed_size) / SUM(original_size) * 100)),2),"%") AS saved_percentage
            FROM compression_history AS i');
    }
}
