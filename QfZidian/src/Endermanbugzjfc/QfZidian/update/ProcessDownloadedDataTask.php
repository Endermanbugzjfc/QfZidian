<?php

declare(strict_types=1);

namespace Endermanbugzjfc\QfZidian\update;

use pocketmine\scheduler\AsyncTask;
use ZipArchive;
use function is_callable;

class ProcessDownloadedDataTask extends AsyncTask
{

    public function __construct(
        protected string $source,
        protected string $dest,
        callable $callback = null
    )
    {
        $this->storeLocal("callback", $callback);
    }

    public function onRun() : void
    {
        $zip = new ZipArchive();
        $ok = $zip->open(
            $this->source
        );
        if ($ok !== true) {
            $this->setResult($ok);
            return;
        }
        $ok2 = $zip->extractTo(
            $this->dest
        );
        $this->setResult($ok2);
    }

    public function onCompletion() : void
    {
        $callback = $this->fetchLocal("callback");
        if (is_callable($callback)) {
            $callback($this->getResult());
        }
    }

}