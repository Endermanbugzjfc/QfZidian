<?php


declare(strict_types=1);

namespace Endermanbugzjfc\QfZidian\update;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Internet;
use function is_callable;

class AutoUpdateTask extends AsyncTask
{

    public function __construct(
        protected string $url,
        callable $callback = null
    )
    {
        $this->storeLocal("callback", $callback);
    }

    public function onRun() : void
    {
        $this->setResult(Internet::getURL($this->url));
    }

    public function onCompletion() : void
    {
        $callback = $this->fetchLocal("callback");
        if (is_callable($callback)) {
            $callback($this->getResult());
        }
    }

}