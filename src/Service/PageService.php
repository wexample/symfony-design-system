<?php

namespace Wexample\SymfonyDesignSystem\Service;

use JetBrains\PhpStorm\Pure;
use Symfony\Component\Routing\RouterInterface;
use Wexample\SymfonyDesignSystem\Controller\AbstractPagesController;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\PageRenderNode;
use Wexample\SymfonyHelpers\Helper\ClassHelper;
use Wexample\SymfonyHelpers\Helper\FileHelper;
use Wexample\SymfonyHelpers\Helper\TextHelper;
use Wexample\SymfonyTranslations\Translation\Translator;

class PageService extends RenderNodeService
{
    #[Pure]
    public function __construct(
        protected AdaptiveResponseService $adaptiveResponseService,
        protected AssetsService $assetsService,
        protected Translator $translator,
        protected RouterInterface $router
    ) {
        parent::__construct(
            $this->assetsService,
            $adaptiveResponseService
        );
    }

    public function pageInit(
        PageRenderNode $page,
        string $pageName,
        bool $useJs
    ) {
        $this->initRenderNode(
            $page,
            $pageName,
            $useJs
        );

        $this->translator->setDomainFromPath(
            $page->getContextType(),
            $pageName
        );
    }

    public function getControllerClassPathFromRouteName(string $routeName): string
    {
        $routes = $this->router->getRouteCollection();

        return $routes->get($routeName)->getDefault('_controller');
    }

    public function buildPageNameFromClassPath(string $methodClassPath): string
    {
        $explode = \explode(ClassHelper::METHOD_SEPARATOR, $methodClassPath);

        // Remove useless namespace part.
        $controllerRelativePath = TextHelper::removePrefix(
            TextHelper::removeSuffix($explode[0], 'Controller'),
            AbstractPagesController::NAMESPACE_PAGES
        );

        // Cut parts.
        $explodeController = \explode(
            ClassHelper::NAMESPACE_SEPARATOR,
            $controllerRelativePath
        );

        // Append method name.
        $explodeController[] = $explode[1];

        // Convert all parts.
        $explodeController = \array_map(
            TextHelper::class.'::toSnake',
            $explodeController
        );

        // Return joined string.
        return AbstractPagesController::RESOURCES_DIR_PAGE.\implode(
            FileHelper::FOLDER_SEPARATOR,
            $explodeController
        );
    }
}
