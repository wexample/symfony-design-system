<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Symfony\Component\Routing\RouterInterface;
use Wexample\SymfonyDesignSystem\Controller\AbstractController;
use Wexample\SymfonyDesignSystem\Controller\AbstractPagesController;
use Wexample\SymfonyDesignSystem\Helper\PageHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\PageRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyHelpers\Attribute\SimpleMethodResolver;
use Wexample\SymfonyHelpers\Class\AbstractBundle;
use Wexample\SymfonyHelpers\Controller\Traits\HasSimpleRoutesControllerTrait;
use Wexample\SymfonyHelpers\Helper\BundleHelper;
use Wexample\Helpers\Helper\ClassHelper;
use Wexample\Helpers\Helper\TextHelper;
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

    public function pageTranslationPathFromRoute(string $route): string
    {
        $controllerMethodPath = $this
            ->getControllerClassPathFromRouteName($route);

        if (ClassHelper::hasAttributes(
            $controllerMethodPath,
            SimpleMethodResolver::class
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

        return $this->buildTranslationPathFromClassPath(
            $controllerMethodPath
        );
    }

    public function buildTranslationPathFromClassPath(string $classPath): string
    {
        [$controllerFullPath, $methodName] = explode(ClassHelper::METHOD_SEPARATOR, $classPath);

        // Remove useless namespace part.
        $controllerName = AbstractController::removeSuffix($controllerFullPath);

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

            return BundleHelper::ALIAS_PREFIX.$controllerBundle::getAlias().'.'.PageHelper::joinNormalizedParts(
                    $explodeController,
                    '.'
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

        return PageHelper::joinNormalizedParts(
            $explodeController,
            '.'
        );
    }
}
