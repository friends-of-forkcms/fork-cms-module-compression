<?php
declare(strict_types=1);

namespace Backend\Modules\Compression\Domain\CompressionHistory\Helpers;

use InvalidArgumentException;

/**
 * Class Helper
 * @package Backend\Modules\Compression\Helper
 */
class Helper
{
    /**
     * Converts a long string of bytes into a readable format e.g KB, MB, GB, TB, YB
     * @param int $bytes
     * @return string
     */
    public static function readableBytes(int $bytes): string
    {
        if ($bytes < 0) {
            throw new InvalidArgumentException('Bytes should be a non-negative integer');
        }

        if ($bytes === 0) {
            return '0 KB';
        }

        $i = floor(log($bytes) / log(1024));
        $sizes = array('bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

        return sprintf('%.02F', $bytes / (1024 ** $i)) * 1 . ' ' . $sizes[$i];
    }
}
