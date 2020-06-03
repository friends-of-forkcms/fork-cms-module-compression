<?php
declare(strict_types=1);

namespace Backend\Modules\Compression\Domain\CompressionSetting\Helpers;

use DirectoryIterator;
use IteratorIterator;
use Symfony\Component\Finder\Finder;

class Helper
{
    /**
     * Build a directory tree as array to transform to JSON
     * @param string $folder_path The root folder path
     * @param int $depth The recursive depth
     * @param array|null $directories
     * @return array
     */
    public static function BuildDirectoryTreeJson($folder_path = '', $depth = 0, $directories = null): array
    {
        $iterator = new IteratorIterator(new DirectoryIterator($folder_path));
        $tree = [];

        foreach ($iterator as $splFileInfo) {
            $node = null;
            if ($splFileInfo->isDot()) {
                continue;
            }

            // If we have a directory, try and get its children
            if ($splFileInfo->isDir()) {
                // Compare the path of this directory with the path of the directories saved in the database. Check the folder if they match.
                $nodeIsChecked = false;
                $currentFolderPath = $splFileInfo->getRealPath();

                if ($directories !== null) {
                    foreach ($directories as $dbDirectory) {
                        if ($dbDirectory['path'] === $currentFolderPath) {
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
                                $nodeIsChecked = true;
                            }

                            break;
                        }
                    }
                }

                // Add the folder count
                $finder = new Finder();
                $imagesCount = $finder
                    ->files()
                    ->name('/\.(jpg|jpeg|png)$/i')
                    ->in($splFileInfo->getRealPath())
                    ->count();

                $node = [
                    'value' => $currentFolderPath,
                    'label' => $splFileInfo->getFilename(),
                    'count' => $imagesCount,
                    'checked' => $nodeIsChecked,
                    'children' => self::BuildDirectoryTreeJson($splFileInfo->getPathname(), $depth + 1, $directories)
                ];
            }

            if ($node) {
                $tree[] = $node;
            }
        }

        return $tree;
    }
}
