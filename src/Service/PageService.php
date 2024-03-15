<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Symfony\Component\Routing\RouterInterface;
use Wexample\SymfonyDesignSystem\Controller\AbstractPagesController;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\PageRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyHelpers\Attribute\IsSimpleMethodResolver;
use Wexample\SymfonyHelpers\Class\AbstractBundle;
use Wexample\SymfonyHelpers\Controller\Traits\HasSimpleRoutesControllerTrait;
use Wexample\SymfonyHelpers\Helper\BundleHelper;
use Wexample\SymfonyHelpers\Helper\ClassHelper;
use Wexample\SymfonyHelpers\Helper\TextHelper;
use Wexample\SymfonyTranslations\Translation\Translator;

class PageService extends RenderNodeService
{
    public function __construct(
        AssetsService $assetsService,
        protected Translator $translator,
        protected RouterInterface $router
    ) {
        parent::__construct(
            $assetsService,
        );
    }

    public function pageInit(
        RenderPass $renderPass,
        PageRenderNode $page,
        string $view
    ): void {
        $this->initRenderNode(
            $page,
            $renderPass,
            $view
        );

        $this->translator->setDomainFromPath(
            $page->getContextType(),
            $view
        );
    }

    public function getControllerClassPathFromRouteName(string $routeName): string
    {
        return $this->router->getRouteCollection()->get($routeName)->getDefault('_controller');
    }

    private function convertClassPathToPageName(array $pathParts): string
    {
        $pathParts = array_map([TextHelper::class, 'toSnake'], $pathParts);
        return implode('.', $pathParts);
    }

    public function buildPageNameFromRoute(string $route): string
    {
        $controllerMethodPath = $this
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

        return $this->buildPageNameFromClassPath(
            $controllerMethodPath
        );
    }

    public function buildPageNameFromClassPath(string $classPath): string
    {
        [$controllerFullPath, $methodName] = explode(ClassHelper::METHOD_SEPARATOR, $classPath);

        // Remove useless namespace part.
        $controllerName = TextHelper::removeSuffix($controllerFullPath, 'Controller');

        /** @var AbstractPagesController $controllerFullPath */
        /** @var AbstractBundle $controllerBundle */
        if ($controllerBundle = $controllerFullPath::getControllerBundle()) {
            $explodeController = explode(
                ClassHelper::NAMESPACE_SEPARATOR,
                $controllerName
            );

            $explodeController = array_splice($explodeController, 3);

            // Append method name.
            $explodeController[] = $methodName;

            return BundleHelper::ALIAS_PREFIX.$controllerBundle::getAlias().'.'.$this->convertClassPathToPageName(
                    $explodeController,
                );
        }
        // Remove useless namespace part.
        $controllerRelativePath = TextHelper::removePrefix(
            $controllerName,
            AbstractPagesController::NAMESPACE_CONTROLLER
        );

        // Cut parts.
        $explodeController = explode(
            ClassHelper::NAMESPACE_SEPARATOR,
            $controllerRelativePath
        );

        // Append method name.
        $explodeController[] = $methodName;

        return $this->convertClassPathToPageName(
            $explodeController,
            $methodName
        );
    }
}
