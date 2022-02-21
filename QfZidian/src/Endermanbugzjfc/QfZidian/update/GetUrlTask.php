<?php


declare(strict_types=1);

namespace Endermanbugzjfc\QfZidian\update;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Internet;
use pocketmine\utils\InternetRequestResult;
use function dirname;
use function file_put_contents;
use function is_callable;
use function mkdir;

class GetUrlTask extends AsyncTask
{

    /**
     * Result types:
     * {@link InternetRequestResult}: Responded.
     * String: Internet exception message.
     * Bool (false): Responded, failed to copy body to dest file.
     * Int: Responded, the count of bytes copied from body to dest file. The {@link InternetRequestResult} is not returned for saving memory. Otherwise the body data will be duplicated.
     * @param string $url
     * @param string|null $dest Overrides existed file.
     * @param callable|null $callback
     */
    public function __construct(
        protected string  $url,
        protected ?string $dest,
        // Async task properties can't have default value. (Back in PHP 7.3)
        callable          $callback = null
    )
    {
        $this->storeLocal("callback", $callback);
    }

    public function onRun() : void
    {
        $result = Internet::getURL(
            $this->url,
            10,
            [],
            $err
        );
        $dest = $this->dest;
        if (
            $result !== null
            and
            $dest !== null
        ) {
            @mkdir(dirname($dest));
            Filesystem::recursiveUnlink($dest);
            $ok = file_put_contents($dest, $result->getBody());
        }
        $this->setResult(
            $ok ?? $result ?? $err
        );
    }

    public function onCompletion() : void
    {
        $callback = $this->fetchLocal("callback");
        if (is_callable($callback)) {
            $callback($this->getResult());
        }
    }

}