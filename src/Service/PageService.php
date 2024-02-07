<?php

namespace Wexample\SymfonyDesignSystem\Service;

use JetBrains\PhpStorm\Pure;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Wexample\SymfonyDesignSystem\Controller\AbstractPagesController;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\PageRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyHelpers\AbstractBundle;
use Wexample\SymfonyHelpers\Helper\ClassHelper;
use Wexample\SymfonyHelpers\Helper\TextHelper;
use Wexample\SymfonyTranslations\Translation\Translator;

class PageService extends RenderNodeService
{
    #[Pure]
    public function __construct(
        AssetsService $assetsService,
        AdaptiveResponseService $adaptiveResponseService,
        KernelInterface $kernel,
        protected Translator $translator,
        protected RouterInterface $router
    ) {
        parent::__construct(
            $assetsService,
            $adaptiveResponseService,
            $kernel
        );
    }

    public function pageInit(
        RenderPass $renderPass,
        PageRenderNode $page,
        string $pagePath,
    ): void {
        $this->initRenderNode(
            $renderPass,
            $page,
            $pagePath,
        );

        $this->translator->setDomainFromPath(
            $page->getContextType(),
            $pagePath
        );
    }

    public function getControllerClassPathFromRouteName(string $routeName): string
    {
        $routes = $this->router->getRouteCollection();

        return $routes->get($routeName)->getDefault('_controller');
    }

    private function buildPageNameFromClassPathComplete(array $parts): string
    {
        // Convert all parts.
        $parts = array_map(
            TextHelper::class.'::toSnake',
            $parts
        );

        return implode(
            '.',
            $parts
        );
    }

    public function buildPageNameFromClassPath(string $methodClassPath): string
    {
        $explode = explode(ClassHelper::METHOD_SEPARATOR, $methodClassPath);
        $methodName = $explode[1];
        /** @var AbstractPagesController $controllerClass */
        $controllerClass = $explode[0];

        // Remove useless namespace part.
        $controllerWithoutSuffix = TextHelper::removeSuffix($explode[0], 'Controller');

        /** @var AbstractBundle $controllerBundle */
        if ($controllerBundle = $controllerClass::getControllerBundle()) {
            $explodeController = explode(
                ClassHelper::NAMESPACE_SEPARATOR,
                $controllerWithoutSuffix
            );

            $explodeController = array_splice($explodeController, 3);

            // Append method name.
            $explodeController[] = $explode[1];

            return '@'.$controllerBundle::getAlias().'.'.$this->buildPageNameFromClassPathComplete(
                    $explodeController,
                );
        }
        // Remove useless namespace part.
        $controllerRelativePath = TextHelper::removePrefix(
            $controllerWithoutSuffix,
            AbstractPagesController::NAMESPACE_CONTROLLER
        );

        // Cut parts.
        $explodeController = explode(
            ClassHelper::NAMESPACE_SEPARATOR,
            $controllerRelativePath
        );

        // Append method name.
        $explodeController[] = $methodName;

        return $this->buildPageNameFromClassPathComplete(
            $explodeController,
            $methodName
        );
    }
}
