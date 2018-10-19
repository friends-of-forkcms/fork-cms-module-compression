<?php

namespace Backend\Modules\Compression\Widgets;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\Widget as BackendBaseWidget;
use Backend\Modules\Compression\Engine\Model as BackendCompressionModel;

/**
 * This widget will show the statistics of the compression module.
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class Statistics extends BackendBaseWidget
{
    /**
     * Execute the widget
     */
    public function execute(): void
    {
        $this->setColumn('right');
        $this->setPosition(1);
        $this->parse();
        $this->display();
    }

    /**
     * Parse into template
     */
    private function parse(): void
    {
        // Fetch statistics
        $statistics = BackendCompressionModel::getStatistics();

        // Format bytes
        $statistics['saved_bytes'] = $this->formatSizeUnits($statistics['saved_bytes']);

        // Compress data available?
        $statistics['statistics_enable'] = (int)$statistics['total_compressed'] == 0 ? false : true;

        // Parse into template
        $this->header->addCSS('Compression.css', 'Compression');
        $this->template->assign('statistics', $statistics);
    }

    /**
     * Format size of bytes properly
     *
     * @param $bytes
     * @return string
     */
    private function formatSizeUnits($bytes): string
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
}
