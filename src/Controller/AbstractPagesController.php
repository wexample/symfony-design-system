<?php

namespace Wexample\SymfonyDesignSystem\Controller;

use Symfony\Component\HttpFoundation\Response;
use Wexample\SymfonyHelpers\Class\AbstractBundle;
use Wexample\SymfonyHelpers\Controller\Traits\HasSimpleRoutesControllerTrait;
use Wexample\SymfonyHelpers\Helper\BundleHelper;
use Wexample\SymfonyHelpers\Helper\FileHelper;
use Wexample\SymfonyTemplate\Helper\TemplateHelper;

abstract class AbstractPagesController extends AbstractDesignSystemController
{
    use HasSimpleRoutesControllerTrait;

    public const NAMESPACE_CONTROLLER = 'App\\Controller\\';

    public const BUNDLE_TEMPLATE_SEPARATOR = '::';


    public static function buildTemplatePath(
        string $view,
        AbstractBundle|string|null $bundleClass = null
    ): string
    {
        $base = '';

        if (str_contains($view, self::BUNDLE_TEMPLATE_SEPARATOR)) {
            $exp = explode(self::BUNDLE_TEMPLATE_SEPARATOR, $view);
            $base = $exp[0] . FileHelper::FOLDER_SEPARATOR . BundleHelper::BUNDLE_PATH_TEMPLATES . $base;
            $view = $exp[1];
        }

        return BundleHelper::ALIAS_PREFIX
            . static::getTemplateLocationPrefix() . '/'
            . $base . $view . TemplateHelper::TEMPLATE_FILE_EXTENSION;
    }

    public static function buildControllerTemplatePath(
        string $pageName,
        string $bundle = null
    ): string
    {
        $bundle = BundleHelper::getRelatedBundle(static::class);

        $parts = TemplateHelper::explodeControllerNamespaceSubParts(static::class, $bundle);
        $parts[] = $pageName;

        return static::buildTemplatePath(TemplateHelper::joinNormalizedParts($parts), $bundle);
    }

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
