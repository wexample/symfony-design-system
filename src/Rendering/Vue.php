<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

use Exception;
use Wexample\SymfonyHelpers\Helper\BundleHelper;
use Wexample\SymfonyHelpers\Helper\FileHelper;
use function explode;
use function implode;
use function md5;
use function microtime;
use function mt_getrandmax;
use function random_int;
use function str_replace;
use function strtolower;

class Vue
{
    public string $id;

    public string $name;

    /**
     * @throws Exception
     */
    public function __construct(public string $path)
    {
        $this->name = $this->buildName($this->path);
        $this->id = $this->name.'-'.md5(random_int(0, mt_getrandmax()).microtime());
    }

    public function buildName(string $path): string
    {
        if (BundleHelper::ALIAS_PREFIX === $path[0]) {
            $path = substr($path, 1);
        }

        return str_replace(
            BundleHelper::ALIAS_PREFIX,
            '',
            strtolower(
                implode(
                    '-',
                    explode(
                        FileHelper::FOLDER_SEPARATOR,
                        $path
                    )
                )
            )
        );
    }
}
