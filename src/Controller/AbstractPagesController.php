<?php

namespace Wexample\SymfonyDesignSystem\Controller;

use Symfony\Component\HttpFoundation\Response;
use Wexample\SymfonyDesignSystem\Helper\TemplateHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyHelpers\Attribute\IsSimpleMethodResolver;
use Wexample\SymfonyHelpers\Class\AbstractBundle;
use Wexample\SymfonyHelpers\Controller\Traits\HasSimpleRoutesControllerTrait;
use Wexample\SymfonyHelpers\Helper\BundleHelper;
use Wexample\SymfonyHelpers\Helper\FileHelper;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

abstract class AbstractPagesController extends AbstractController
{
    use HasSimpleRoutesControllerTrait;

    protected string $viewPathPrefix = '';

    public const NAMESPACE_CONTROLLER = 'App\\Controller\\';

    public const NAMESPACE_PAGES = self::NAMESPACE_CONTROLLER.'Pages\\';

    public const RESOURCES_DIR_PAGE = VariableHelper::PLURAL_PAGE.FileHelper::FOLDER_SEPARATOR;

    public const BUNDLE_TEMPLATE_SEPARATOR = '::';

    protected function buildTemplatePath(
        string $view,
        AbstractBundle|string|null $bundleClass = null
    ): string {
        $base = self::RESOURCES_DIR_PAGE;
        $bundleClass = $bundleClass ?: $this->getControllerBundle();

        if (str_contains($view, self::BUNDLE_TEMPLATE_SEPARATOR)) {
            $exp = explode(self::BUNDLE_TEMPLATE_SEPARATOR, $view);
            $base = $exp[0].FileHelper::FOLDER_SEPARATOR.BundleHelper::BUNDLE_PATH_TEMPLATES.$base;
            $view = $exp[1];
        }

        return BundleHelper::ALIAS_PREFIX
            .($bundleClass ? $bundleClass::getAlias() : 'front').'/'
            .$base.$this->viewPathPrefix.$view.TemplateHelper::TEMPLATE_FILE_EXTENSION;
    }

    protected function renderPage(
        string $pageName,
        array $parameters = [],
        Response $response = null,
        AbstractBundle|string $bundle = null,
        RenderPass $renderPass = null
    ): Response {
        return $this->adaptiveRender(
            $this->buildTemplatePath($pageName, $bundle),
            $parameters,
            $response,
            renderPass:$renderPass
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
