<?php


declare(strict_types=1);

namespace Endermanbugzjfc\QfZidian\config;

use function trim;

class ConfigRoot
{

    public string $AutoUpdateRepo = "qloog/sensitive_words";

    public function getAutoUpdateRepo() : string {
        return trim($this->AutoUpdateRepo);
    }

}