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
                    yield from $this->downloadUpdates();
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
                    $oldSha ?? null,
                    $newSha
                );
            }
            return "HTTP code $code";
        }
        return false; // Auto update disabled.
    }

    protected function downloadUpdates() : Generator
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