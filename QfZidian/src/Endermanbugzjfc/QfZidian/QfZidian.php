<?php


declare(strict_types=1);

namespace Endermanbugzjfc\QfZidian;

use pocketmine\plugin\PluginBase;

class QfZidian extends PluginBase
{

    protected function onEnable() : void
    {

    }

    protected static QfZidian $instance;

    protected function onLoad() : void
    {
        self::$instance = $this;
    }

    /**
     * @return QfZidian
     */
    public static function getInstance() : QfZidian
    {
        return self::$instance;
    }

}