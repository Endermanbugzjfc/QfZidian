<?php

declare(strict_types=1);

namespace Endermanbugzjfc\QfZidian\update;

use pocketmine\scheduler\AsyncTask;
use ZipArchive;
use function file_get_contents;
use function is_callable;
use function serialize;
use function unserialize;

class ProcessDownloadedDataTask extends AsyncTask
{

    protected string $readFilesSerialized;

    public function __construct(
        protected string $source,
        protected string $dest,
        array            $readFiles,
        callable         $callback = null
    )
    {
        $this->readFilesSerialized = serialize($readFiles);
        $this->storeLocal("callback", $callback);
    }

    public function onRun() : void
    {
        $zip = new ZipArchive();
        $ok = $zip->open(
            $this->source
        );
        try {
            if ($ok !== true) {
                return;
            }
            $ok2 = $zip->extractTo(
                $this->dest
            );
            if ($ok2 !== true) {
                return;
            }

            $readFiles = unserialize($this->readFilesSerialized);
            foreach ($readFiles as $file) {
                $content = file_get_contents($file);
                $fileResults[$file] = $content;
            }
        } finally {
            $this->setResult([
                $ok,
                $ok2 ?? null,
                $fileResults ?? []
            ]);
        }
    }

    public function onCompletion() : void
    {
        $callback = $this->fetchLocal("callback");
        if (is_callable($callback)) {
            $callback($this->getResult());
        }
    }

}