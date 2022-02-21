<?php


declare(strict_types=1);

namespace Endermanbugzjfc\QfZidian;

use Endermanbugzjfc\QfZidian\config\ConfigRoot;
use Endermanbugzjfc\QfZidian\update\AutoUpdateEvent;
use Endermanbugzjfc\QfZidian\update\GetUrlTask;
use Generator;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\InternetRequestResult;
use SOFe\AwaitGenerator\Await;
use function basename;
use function file_exists;
use function file_get_contents;
use function is_string;
use function json_decode;
use function urlencode;

class QfZidian extends PluginBase
{

    protected function onEnable() : void
    {
        $this->reload();
    }

    /*
     * I don't think this plugin would need to use database.
     * Because the words list from qloog/sensitive_words is only 308.6 KB at total.
     * (25.6 KB + 283 KB)
     */
    /**
     * @var string[]
     */
    protected array $dictionary;

    /**
     * @return array
     */
    public function getDictionary() : array
    {
        return $this->dictionary;
    }

    /**
     * @param array $dictionary
     */
    public function setDictionary(string ...$dictionary) : void
    {
        $this->dictionary = $dictionary;
    }

    public function reload(
        ?callable $callback = null
    ) : void
    {
        Await::f2c(function () use
        (
            $callback
        ) : Generator {
            $this->getLogger()->info("Reloading...");
            $this->reloadConfigStruct();
            $hasUpdates = yield from $this->fetchUpdates();
            if (is_string($hasUpdates)) {
                $this->getLogger()->error(
                    "Failed to fetch dictionary content updates: $hasUpdates"
                );
            } elseif ($hasUpdates instanceof AutoUpdateEvent) {
                $hasUpdates->call();
                if (!$hasUpdates->isCancelled()) {
                    $this->getLogger()->info(
                        "Updating dictionary content... ($hasUpdates)"
                    );
                    yield from $this->downloadUpdates($hasUpdates);
                } else {
                    $this->getLogger()->debug(
                        "An auto update event has been cancelled ($hasUpdates)"
                    );
                }
            }
            $callback();
        });
    }

    protected ConfigRoot $configStruct;

    /**
     * @return ConfigRoot
     */
    public function getConfigStruct() : ConfigRoot
    {
        return $this->configStruct;
    }

    protected function reloadConfigStruct() : void
    {
    }

    protected function fetchUpdates() : Generator
    {
        $config = $this->getConfigStruct();
        $repo = $config->getAutoUpdateRepo();
        if ($repo !== "") {
            $fetchUpdates = new GetUrlTask(
                urlencode(
                    "https://api.github.com/repos/$repo/commits?per_page=1"
                ),
                null,
                yield Await::RESOLVE
            /*
             * To anyone who is learning Await-Generator like me,
             * you should add a callback argument for both success and failure. And use Await::REJECT.
             * I'm not doing it here just because of laziness. :>
             */
            );
            $this->getServer()->getAsyncPool()->submitTask($fetchUpdates);
            $result = yield Await::ONCE;
            if (!$result instanceof InternetRequestResult) {
                return $result; // Error message string.
            }
            $code = $result->getCode();
            if ($code !== 200) {
                $resultArray = json_decode(
                    $result->getBody(),
                    true
                );
                $newSha = $resultArray["sha"] ?? "";
                $lastShaFile = $this->getDataFolder()
                    . ".last_commit_sha.txt";
                if (file_exists($lastShaFile)) {
                    $oldSha = file_get_contents($lastShaFile);
                    if ($newSha === $lastShaFile) {
                        return false;
                    }
                }
                return new AutoUpdateEvent(
                    urlencode(
                        "https://github.com/$repo/$newSha"
                    ),
                    $lastShaFile,
                    $file = $this->getDataFolder()
                        . "qloog-sensitive_words.zip",
                    basename($file, ".zip"),
                    // Hard code file name
                    $oldSha ?? null,
                    $newSha
                );
            }
            return "HTTP code $code";
        }
        return false; // Auto update disabled.
    }

    protected function downloadUpdates(
        AutoUpdateEvent $event
    ) : Generator
    {
        $downloadUpdates = new GetUrlTask(
            $event->getUrl(),
            $event->getContentArchiveFile(),
            yield Await::RESOLVE
        );
        $this->getServer()->getAsyncPool()->submitTask($downloadUpdates);
        $result = yield Await::ONCE;
        if (is_string($result)) {
            $this->getLogger()->error(
                "Failed to download the dictionary content file: $result"
            );
        } elseif ($result === false) {
            $this->getLogger()->error(
                "Failed to override the existed dictionary content file"
            );
        } else {
            $this->getLogger()->debug(
                "Copied $result bytes"
            );
            return true;
        }
        return false;
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