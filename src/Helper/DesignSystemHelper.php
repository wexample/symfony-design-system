<?php

namespace Wexample\SymfonyDesignSystem\Helper;

use Wexample\SymfonyHelpers\Helper\BundleHelper;
use Wexample\SymfonyHelpers\Helper\FileHelper;

class DesignSystemHelper
{
    public final const BUNDLE_NAME = 'SymfonyDesignSystemBundle';
    public final const FOLDER_FRONT_ALIAS = BundleHelper::ALIAS_PREFIX.DesignSystemHelper::BUNDLE_NAME.FileHelper::FOLDER_SEPARATOR;
    public final const CONFIG_PARAMETER_FRONTS = self::BUNDLE_NAME . '.fronts';
}
