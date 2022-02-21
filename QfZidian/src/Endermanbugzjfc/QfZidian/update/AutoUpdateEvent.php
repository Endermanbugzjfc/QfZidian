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

    public function __construct(
        protected string $url,
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
     * WARNING: Changing this will not update the URL itself.
     * @param string|null $newSha
     */
    public function setNewSha(?string $newSha) : void
    {
        $this->newSha = $newSha;
    }

}