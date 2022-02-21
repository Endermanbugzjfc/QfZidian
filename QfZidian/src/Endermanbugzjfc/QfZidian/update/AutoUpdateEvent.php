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
     * Cancelling this event will stop the plugin from downloading the file and from updating the last Sha file, also from reloading dictionary content.
     * @param string $url URL to the updated dictionary content source file.
     * @param string $newSha Will be displayed in log message.
     * @param string|null $lastShaFile File to save the new Sha which will be used in the next auto update.
     * @param string|null $oldSha Will be displayed in log message if is not null.
     */
    public function __construct(
        protected string  $url,
        protected string  $newSha,
        protected ?string $lastShaFile = null,
        protected ?string $oldSha = null
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
     * @param string $url
     */
    public function setUrl(string $url) : void
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getNewSha() : string
    {
        return $this->newSha;
    }

    /**
     * > WARNING: Changing this will not update the URL itself.
     * @param string $newSha
     */
    public function setNewSha(string $newSha) : void
    {
        $this->newSha = $newSha;
    }

    /**
     * @return string|null
     */
    public function getLastShaFile() : ?string
    {
        return $this->lastShaFile;
    }

    /**
     * @param string|null $lastShaFile
     */
    public function setLastShaFile(?string $lastShaFile) : void
    {
        $this->lastShaFile = $lastShaFile;
    }

}