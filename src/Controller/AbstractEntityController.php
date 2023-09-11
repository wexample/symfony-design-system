<?php

namespace Wexample\SymfonyDesignSystem\Controller;

use Wexample\SymfonyDesignSystem\Controller\Traits\EntityControllerTrait;

/**
 * Reference trait and build simple constructor.
 * Use this class as shorthand if you have only one parent inherited class.
 */
abstract class AbstractEntityController extends AbstractController
{
    use EntityControllerTrait;
}
