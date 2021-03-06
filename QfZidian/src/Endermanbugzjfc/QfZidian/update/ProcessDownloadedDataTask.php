<?php

declare(strict_types=1);

namespace Endermanbugzjfc\QfZidian\update;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Filesystem;
use ZipArchive;
use function file_exists;
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
        protected bool   $override,
        array            $readFiles,
        callable         $callback = null
    )
    {
        $this->readFilesSerialized = serialize($readFiles);
        $this->storeLocal("callback", $callback);
    }

    public function onRun() : void
    {
        try {
            $dest = $this->dest;
            if (
                $this->override
                or
                !file_exists($dest)
            ) {
                Filesystem::recursiveUnlink($dest);
                $zip = new ZipArchive();
                $ok = $zip->open(
                    $this->source
                );
                if ($ok !== true) {
                    return;
                }
                $ok2 = $zip->extractTo(
                    $dest
                );
                if ($ok2 !== true) {
                    return;
                }
            }

            $readFiles = unserialize($this->readFilesSerialized);
            foreach ($readFiles as $file) {
                $content = file_get_contents($file);
                $fileResults[$file] = $content;
            }
        } finally {
            $this->setResult([
                $ok ?? null,
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