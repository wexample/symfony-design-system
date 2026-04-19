<?php

namespace Wexample\SymfonyDesignSystem\Controller\Pages\DesignSystem;

use Wexample\SymfonyDesignSystem\Traits\SymfonyDesignSystemBundleClassTrait;
use Wexample\SymfonyLoader\Controller\AbstractPagesController;

abstract class AbstractDesignSystemGenericController extends AbstractPagesController
{
    use SymfonyDesignSystemBundleClassTrait;
}
