<?php


declare(strict_types=1);

namespace Endermanbugzjfc\QfZidian;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\plugin\PluginEvent;

class AutoUpdateEvent extends PluginEvent implements Cancellable
{
    use CancellableTrait;

    public function __construct()
    {
        parent::__construct(QfZidian::getInstance());
    }

}