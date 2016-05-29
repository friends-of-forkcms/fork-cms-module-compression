<?php

namespace Backend\Modules\Compression\Engine;

use DirectoryIterator;
use IteratorIterator;
use Symfony\Component\Finder\Finder;

class Helper
{
    /**
     * Build a directory tree in html list
     *
     * @param string $folder_path The root folder path
     * @param int $depth The recursive depth
     * @param array|null $directories
     *
     * @return string A directory tree in HTML
     */
    public static function BuildDirectoryTreeHtml($folder_path = '', $depth = 0, $directories = null)
    {
        $iterator = new IteratorIterator(new DirectoryIterator($folder_path));

        $r = '<ul>';

        foreach ($iterator as $splFileInfo) {
            if ($splFileInfo->isDot()) {
                continue;
            }

            // If we have a directory, try and get its children
            if ($splFileInfo->isDir()) {
                // Compare the path of this directory with the path of the directories saved in the database. Check the folder if they match.
                $checkFolder = false;
                $currentFolderPath = $splFileInfo->getRealPath();

                if ($directories !== null) {
                    foreach ($directories as $dbDirectory) {
                        if ($dbDirectory['path'] == $currentFolderPath) {
                            // Only check if it's the deepest child. Create new array with only path's.
                            // Search possible child path in other directory paths. If it doesn't match, then it's unique and no parent.
                            $check = true;
                            foreach ($directories as $dirs) {
                                if ($dbDirectory['path'] !== $dirs['path']) {
                                    if (stripos($dirs['path'], $dbDirectory['path']) !== false) {
                                        $check = false;
                                        break;
                                    }
                                }
                            }

                            if ($check) {
                                $checkFolder = true;
                            }
                            break;
                        }
                    }
                }

                if ($checkFolder) {
                    $r .= '<li class="checked" data-path="' . $currentFolderPath . '">';
                } else {
                    $r .= '<li data-path="' . $currentFolderPath . '">';
                }

                // Add the filename to the li element
                $r .= $splFileInfo->getFilename();

                // Add the folder count
                $finder = new Finder();
                $folderCount = $finder
                    ->files()
                    ->name('/\.(jpg|jpeg|png)$/i')
                    ->in($splFileInfo->getRealPath())
                    ->count();
                $r .= ' (' . $folderCount . ')';

                // Get the nodes
                $nodes = self::BuildDirectoryTreeHtml($splFileInfo->getPathname(), $depth + 1, $directories);

                // Only add the nodes if we have some
                if (!empty($nodes)) {
                    $r .= $nodes;
                }

                $r .= '</li>';
            }
        }

        $r .= '</ul>';

        return $r;
    }
}
