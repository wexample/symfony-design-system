<?php

namespace Wexample\SymfonyDesignSystem\Controller;

use Symfony\Component\HttpFoundation\Response;
use Wexample\SymfonyDesignSystem\Helper\PageHelper;
use Wexample\SymfonyDesignSystem\Helper\TemplateHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\AdaptiveResponseService;
use Wexample\SymfonyDesignSystem\Service\LayoutService;
use Wexample\SymfonyDesignSystem\Service\PageService;
use Wexample\SymfonyDesignSystem\Service\RenderPassBagService;
use Wexample\SymfonyHelpers\Attribute\IsSimpleMethodResolver;
use Wexample\SymfonyHelpers\Class\AbstractBundle;
use Wexample\SymfonyHelpers\Controller\Traits\HasSimpleRoutesControllerTrait;
use Wexample\SymfonyHelpers\Helper\BundleHelper;
use Wexample\SymfonyHelpers\Helper\FileHelper;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

abstract class AbstractPagesController extends AbstractController
{
    use HasSimpleRoutesControllerTrait;

    public const NAMESPACE_CONTROLLER = 'App\\Controller\\';

    public const NAMESPACE_PAGES = self::NAMESPACE_CONTROLLER.'Pages\\';

    public const RESOURCES_DIR_PAGE = VariableHelper::PLURAL_PAGE.FileHelper::FOLDER_SEPARATOR;

    public const BUNDLE_TEMPLATE_SEPARATOR = '::';

    public function __construct(
        AdaptiveResponseService $adaptiveResponseService,
        LayoutService $layoutService,
        RenderPassBagService $renderPassBagService,
        protected PageService $pageService
    ) {
        parent::__construct(
            $adaptiveResponseService,
            $layoutService,
            $renderPassBagService);
    }

    protected function buildTemplatePath(
        string $view,
        AbstractBundle|string|null $bundleClass = null
    ): string {
        $base = '';
        $bundleClass = $bundleClass ?: $this->getControllerBundle();

        if (str_contains($view, self::BUNDLE_TEMPLATE_SEPARATOR)) {
            $exp = explode(self::BUNDLE_TEMPLATE_SEPARATOR, $view);
            $base = $exp[0].FileHelper::FOLDER_SEPARATOR.BundleHelper::BUNDLE_PATH_TEMPLATES.$base;
            $view = $exp[1];
        }

        return BundleHelper::ALIAS_PREFIX
            .($bundleClass ? $bundleClass::getAlias() : 'front').'/'
            .$base.$view.TemplateHelper::TEMPLATE_FILE_EXTENSION;
    }

    protected function buildControllerTemplatePath(
        string $pageName,
        string $bundle = null
    ): string {
        $parts = PageHelper::explodeControllerNamespaceSubParts(static::class, $bundle);
        $parts[] = $pageName;

        return $this->buildTemplatePath(PageHelper::joinNormalizedParts($parts), $bundle);
    }

    protected function renderPage(
        string $pageName,
        array $parameters = [],
        Response $response = null,
        AbstractBundle|string $bundle = null,
        RenderPass $renderPass = null
    ): Response {
        return $this->adaptiveRender(
            $this->buildControllerTemplatePath($pageName, $bundle),
            $parameters,
            $response,
            renderPass: $renderPass
        );
    }

    #[IsSimpleMethodResolver]
    public function simpleRoutesResolver(string $routeName): Response
    {
        return $this->renderPage(
            $routeName,
        );
    }
}
