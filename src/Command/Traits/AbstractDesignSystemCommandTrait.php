<?php

namespace Wexample\SymfonyDesignSystem\Command\Traits;

use Wexample\SymfonyDesignSystem\WexampleSymfonyDesignSystemBundle;

trait AbstractDesignSystemCommandTrait
{
    public static function getBundle(): string
    {
        return WexampleSymfonyDesignSystemBundle::class;
    }
}