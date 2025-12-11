<?php

namespace Wexample\SymfonyDesignSystem\Controller;

use Symfony\Component\HttpFoundation\Response;
use Wexample\SymfonyHelpers\Controller\Traits\HasSimpleRoutesControllerTrait;

abstract class AbstractPagesController extends AbstractDesignSystemController
{
    use HasSimpleRoutesControllerTrait;

    protected function renderPage(
        string $pageName,
        array $parameters = [],
        Response $response = null,
        AbstractBundle|string $bundle = null,
        RenderPass $renderPass = null
    ): Response
    {
        # TODO
        return new Response("TODO");
    }
}
