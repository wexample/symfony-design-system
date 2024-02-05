<?php

namespace Wexample\SymfonyDesignSystem\Command;

use Wexample\SymfonyDesignSystem\Command\Traits\AbstractSymfonyDesignSystemBundleCommandTrait;
use Wexample\SymfonyHelpers\Command\AbstractCheckNodeInstallCommand;

class CheckNodeInstallCommand extends AbstractCheckNodeInstallCommand
{
    use AbstractSymfonyDesignSystemBundleCommandTrait;
}
