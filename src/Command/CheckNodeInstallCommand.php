<?php

namespace Wexample\SymfonyDesignSystem\Command;

use Wexample\SymfonyDesignSystem\Command\Traits\AbstractDesignSystemCommandTrait;
use Wexample\SymfonyHelpers\Command\AbstractCheckNodeInstallCommand;

class CheckNodeInstallCommand extends AbstractCheckNodeInstallCommand
{
    use AbstractDesignSystemCommandTrait;
}
