<?php

namespace Wexample\SymfonyDesignSystem\Command\Traits;

use Wexample\SymfonyDesignSystem\WexampleSymfonyDesignSystemBundle;

trait AbstractSymfonyDesignSystemBundleCommandTrait
{
    public static function getBundleClassName(): string
    {
        return WexampleSymfonyDesignSystemBundle::class;
    }
}
