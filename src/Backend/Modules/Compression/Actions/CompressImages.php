<?php
declare(strict_types=1);

namespace Backend\Modules\Compression\Actions;

use Backend\Core\Engine\Base\Action;
use Backend\Modules\Compression\Domain\CompressionHistory\Command\CreateCompressionHistoryRecord;
use Backend\Modules\Compression\Domain\CompressionHistory\Helpers\Helper;
use Backend\Modules\Compression\Exception\FileNotFoundException;
use Backend\Modules\Compression\Domain\CompressionHistory\CompressionHistoryRepository;
use Backend\Modules\Compression\Domain\CompressionSetting\CompressionSetting;
use Backend\Modules\Compression\Domain\CompressionSetting\CompressionSettingRepository;
use Backend\Modules\Compression\Exception\ResponseErrorException;
use Backend\Modules\Compression\Exception\TooManyRequestsException;
use Backend\Modules\Compression\Http\TinyPngApiClient;
use InvalidArgumentException;
use SplStack;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Translation\Exception\InvalidResourceException;

/**
 * In this class, we create a stream of SSE (Server-Sent Events) while compressing images to send realtime feedback to
 * the frontend app (Console tab).
 * @package Backend\Modules\Compression\Actions
 */
class CompressImages extends Action
{
    private const EVENT_NAME = "compression-event";
    private const EVENT_STREAM_END_DELIMITER = "END-OF-STREAM";

    public function execute(): void
    {
        // Stops PHP from checking for user disconnect
        ignore_user_abort(true);

        // How long PHP script stays running/SSE connection stays open (seconds)
        set_time_limit(0);

        // Avoid session locks
        session_write_close();

        // Headers for streaming, must be processed line by line.
        $statusCode = 200;
        header('Connection: keep-alive', false, $statusCode);
        header('Content-Type: text/event-stream; charset=UTF-8', true, $statusCode);
        header('Cache-Control: no-cache', false, $statusCode);
        header('X-Accel-Buffering: no', false, $statusCode); // Disables FastCGI Buffering on Nginx.
        header('HTTP/1.1 200 OK', true, $statusCode);

        // Create a client and stack of images to process
        $client = TinyPngApiClient::createFromModuleSettings($this->get('fork.settings'));
        $imagesStack = $this->getImagesFromFolders();

        // Validate that there are images to process
        if ($imagesStack->isEmpty()) {
            $this->sendCompressionEvent("No images to compress found in the selected folders...");
            $this->closeStream(); // Make sure we don't execute fork cms logic after this
        }
        $currentCreditsStatus = $client->getMonthlyCompressionCount() . "/" . $client::MAX_FREE_CREDITS;
        $this->sendCompressionEvent("Found {$imagesStack->count()} image(s) to compress ($currentCreditsStatus credits)");

        while (!$imagesStack->isEmpty()) {
            if (connection_aborted() === 1) {
                echo "id: Disconnected connection";
                ob_flush();
                flush();
                return;
            }

            // Take one image from stack
            /** @var SplFileInfo $image */
            $image = $imagesStack->pop();

            // Start compressing the image
            $this->sendCompressionEvent("Starting compression of " . $image->getFilename());

            try {
                $compressionSource = $client->fromFile($image->getRealPath());
                $compressionSource->toFile($image->getRealPath());

                // Write to history
                // The command bus will handle the saving of the history record in the database.
                $historyRecord = new CreateCompressionHistoryRecord(
                    $image,
                    $compressionSource->getInputSize(),
                    $compressionSource->getOutputSize()
                );
                $this->get('command_bus')->handle($historyRecord);

                // Send succesful event message
                $this->sendCompressionEvent(sprintf(
                    "Finished compression of image %s. Saved %s (%s%%).",
                    $image->getFilename(),
                    Helper::readableBytes($compressionSource->getSavedBytes()),
                    $compressionSource->getSavedPercentage()
                ));
            } catch (TooManyRequestsException $e) {
                $this->sendCompressionEvent("Error compressing image " . $image->getFilename() . ": " . $e->getMessage());
                $this->closeStream();
            } catch (FileNotFoundException | InvalidResourceException | ResponseErrorException $e) {
                $this->sendCompressionEvent("Error compressing image " . $image->getFilename() . ": " . $e->getMessage());
            }
        }

        if ($imagesStack->isEmpty()) {
            $this->closeStream();
        }
    }

    private function sendCompressionEvent(string $data): void
    {
        echo sprintf(
            "id: %s\nevent: %s\ndata: %s\n\n",
            uniqid('', true),
            self::EVENT_NAME,
            $data
        );
        ob_flush();
        flush();
    }

    /**
     * Create a list of every image in the folders we can process, and add them to a stack to make it easier for processing.
     * @return SplStack
     */
    private function getImagesFromFolders(): SplStack
    {
        $images = [];
        $finder = new Finder();

        $settingsRepository = $this->getSettingsRepository();
        $settings = $settingsRepository->findAll();

        /** @var CompressionSetting $setting */
        foreach ($settings as $setting) {
            try {
                $iterator = $finder
                    ->files()
                    ->name('/\.(jpg|jpeg|png)$/i')
                    ->depth('== 0')
                    ->in($setting->getPath());

                /** @var SplFileInfo $imageFile */
                foreach ($iterator as $imageFile) {
                    // Find the image to see if it was processed before
                    $compressionHistoryRecord = $this->getHistoryRepository()
                        ->findBy([
                            'path' => $imageFile->getRealPath(),
                            'checksum' => sha1_file($imageFile->getRealPath())
                        ]);
                    if (!empty($compressionHistoryRecord)) {
                        continue;
                    }

                    $images[] = $imageFile;
                }
            } catch (InvalidArgumentException $e) {
                $this->sendCompressionEvent("Error: cannot process folder: " . $e->getMessage());
            }
        }

        // Create a stack of images so we can easily take an image, process it and take the next one
        return array_reduce(array_reverse($images), static function (SplStack $stack, $path) {
            $stack->push($path);
            return $stack;
        }, new SplStack());
    }

    private function getSettingsRepository(): CompressionSettingRepository
    {
        return $this->get('compression.repository.compression_setting');
    }

    private function getHistoryRepository(): CompressionHistoryRepository
    {
        return $this->get('compression.repository.compression_history');
    }

    private function closeStream(): void
    {
        $this->sendCompressionEvent(self::EVENT_STREAM_END_DELIMITER);
        exit(); // Make sure we don't do fork cms logic after this
    }
}
