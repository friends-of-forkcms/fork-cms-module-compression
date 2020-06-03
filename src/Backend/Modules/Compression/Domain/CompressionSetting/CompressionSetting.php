<?php
declare(strict_types=1);

namespace Backend\Modules\Compression\Domain\CompressionSetting;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="CompressionSettingRepository")
 * @ORM\HasLifecycleCallbacks
 */
class CompressionSetting
{
    /**
     * @var string
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $path;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    private $createdOn;

    /**
     * @param string $path
     * @return static
     */
    public static function createFromPath(string $path) : self
    {
        return new self(basename($path), $path);
    }

    /**
     * CompressionSetting constructor.
     * @param string $title
     * @param string $path
     */
    private function __construct(string $title, string $path)
    {
        $this->title = $title;
        $this->path = $path;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist(): void
    {
        $this->createdOn = new DateTime();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return DateTime
     */
    public function getCreatedOn(): DateTime
    {
        return $this->createdOn;
    }
}
