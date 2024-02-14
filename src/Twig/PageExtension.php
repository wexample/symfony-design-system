<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Service\PageService;
use Wexample\SymfonyHelpers\Attribute\IsSimpleMethodResolver;
use Wexample\SymfonyHelpers\Controller\AbstractController;
use Wexample\SymfonyHelpers\Controller\Traits\HasSimpleRoutesControllerTrait;
use Wexample\SymfonyHelpers\Helper\ClassHelper;
use Wexample\SymfonyHelpers\Helper\TextHelper;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;

class PageExtension extends AbstractExtension
{
    public function __construct(
        private readonly PageService $pageService,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'page_name_from_route',
                [
                    $this,
                    'pageNameFromRoute',
                ]
            ),
        ];
    }

    public function pageNameFromRoute(string $route): string
    {
        $controllerMethodPath = $this
            ->pageService
            ->getControllerClassPathFromRouteName($route);

        if (ClassHelper::hasAttributes(
            $controllerMethodPath,
            IsSimpleMethodResolver::class
        )) {
            /** @var HasSimpleRoutesControllerTrait $classPath */
            $classPath = TextHelper::getFirstChunk(
                $controllerMethodPath,
                ClassHelper::METHOD_SEPARATOR,
            );

            $methodAlias = substr($route, strlen($classPath::getControllerRouteAttribute()->getName()));

            /** @var string $classPath */
            $controllerMethodPath = ($classPath.ClassHelper::METHOD_SEPARATOR.TextHelper::toCamel($methodAlias));
        }

        return $this->pageService->buildPageNameFromClassPath(
            $controllerMethodPath
        );
    }
}
