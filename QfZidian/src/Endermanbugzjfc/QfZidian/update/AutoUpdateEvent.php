<?php


declare(strict_types=1);

namespace Endermanbugzjfc\QfZidian\update;

use Endermanbugzjfc\QfZidian\QfZidian;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\plugin\PluginEvent;

class AutoUpdateEvent extends PluginEvent implements Cancellable
{
    use CancellableTrait;

    /**
     * Cancelling this event will stop the plugin from downloading the file also from reloading dictionary content.
     * @param string $url URL to the updated dictionary content source file.
     * @param string|null $lastShaFile File to save the new Sha which will be used in the next auto update.
     * @param string|null $oldSha Will be displayed in log message.
     * @param string|null $newSha Will be displayed in log message.
     */
    public function __construct(
        protected string $url,
        protected ?string $lastShaFile = null,
        protected ?string $oldSha = null,
        protected ?string $newSha = null
    )
    {
        parent::__construct(QfZidian::getInstance());
    }

    /**
     * @return string
     */
    public function getUrl() : string
    {
        return $this->url;
    }

    /**
     * @return string|null
     */
    public function getOldSha() : ?string
    {
        return $this->oldSha;
    }

    /**
     * @return string|null
     */
    public function getNewSha() : ?string
    {
        return $this->newSha;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url) : void
    {
        $this->url = $url;
    }

    /**
     * > WARNING: Changing this will not update the URL itself.
     * @param string|null $newSha
     */
    public function setNewSha(?string $newSha) : void
    {
        $this->newSha = $newSha;
    }

    /**
     * @return string
     */
    public function getLastShaFile() : string
    {
        return $this->lastShaFile;
    }

    /**
     * @param string $lastShaFile
     */
    public function setLastShaFile(string $lastShaFile) : void
    {
        $this->lastShaFile = $lastShaFile;
    }

}