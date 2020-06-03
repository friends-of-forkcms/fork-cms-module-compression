<?php
declare(strict_types=1);

namespace Backend\Modules\Compression\Widgets;

use Backend\Core\Engine\Base\Widget as BackendBaseWidget;
use Backend\Modules\Compression\Domain\CompressionHistory\CompressionHistoryRepository;
use Backend\Modules\Compression\Helper\Helper;

/**
 * This widget will show the statistics of the compression module.
 */
class Statistics extends BackendBaseWidget
{
    /**
     * @var array
     */
    private $compressionStats;

    public function execute(): void
    {
        $this->loadData();
        $this->parse();
        $this->display();
    }

    private function loadData(): void
    {
        /** @var CompressionHistoryRepository $repository */
        $repository = $this->get('compression.repository.compression_history');
        $this->compressionStats = $repository->getStatistics();
    }

    /**
     * Parse into template
     */
    private function parse(): void
    {
        // Parse into template
        $this->header->addCSS('Compression.css', 'Compression');
        $this->template->assign('statistics', $this->compressionStats);
    }
}
