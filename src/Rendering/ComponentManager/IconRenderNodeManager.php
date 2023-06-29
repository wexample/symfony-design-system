<?php

namespace Wexample\SymfonyDesignSystem\Rendering\ComponentManager;

use Exception;
use Wexample\SymfonyDesignSystem\Rendering\ComponentRenderNodeManager;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\ComponentRenderNode;
use Wexample\SymfonyDesignSystem\Service\AssetsService;
use Wexample\SymfonyDesignSystem\WexampleSymfonyDesignSystemBundle;
use Wexample\SymfonyHelpers\Helper\BundleHelper;
use Wexample\SymfonyHelpers\Helper\FileHelper;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

class IconRenderNodeManager extends ComponentRenderNodeManager
{
    private array $icons = [];

    public const DIR_FONTAWESOME_SVG = 'fontawesome'.FileHelper::FOLDER_SEPARATOR.'svgs'.FileHelper::FOLDER_SEPARATOR;

    public const DIR_BUILD_ICONS = 'icons'.FileHelper::FOLDER_SEPARATOR;

    public const REL_PATH_LIB_FONTAWESOME_SVG = FileHelper::FOLDER_SEPARATOR
    .'vendor/wexample/symfony-design-system/'
    .BundleHelper::BUNDLE_PATH_RESOURCES
    .'fonts'
    .self::DIR_FONTAWESOME_SVG;

    private int $iconHeight = 512;

    private array $renderedIcons = [];

    private int $translationX = 0;

    /**
     * @throws \Exception
     */
    public function createComponent(ComponentRenderNode $componentRenderNode)
    {
        $key = $componentRenderNode->options['group'].'-'.$componentRenderNode->options['name'];

        // Avoid duplicates.
        if (!isset($this->icons[$key])) {
            $svgPath =
                BundleHelper::getBundleRootPath(
                    WexampleSymfonyDesignSystemBundle::class,
                    $this->kernel
                )
                    .'src/'
                .BundleHelper::BUNDLE_PATH_RESOURCES
                .'fonts/'
                .self::DIR_FONTAWESOME_SVG
                .$componentRenderNode->options['group']
                .FileHelper::FOLDER_SEPARATOR
                .$componentRenderNode->options['name']
                .'.'.FileHelper::FILE_EXTENSION_SVG;

            if (is_file($svgPath)) {
                $svg = file_get_contents($svgPath);

                try {
                    $xml = new \SimpleXMLElement($svg);
                } catch (Exception) {
                    $xml = null;
                }

                if ($xml) {
                    $pathXml = $xml->children();

                    $pathXml->addAttribute('fill', 'currentColor');
                    $pathXml->addAttribute(
                        'transform',
                        'translate('
                        .$this->translationX.',0)'
                    );

                    $this->renderedIcons[] = '<symbol id="'.$key.'">'.$pathXml->asXML().'</symbol>';

                    // Grab with.
                    $width = (int) explode(' ', (string) $xml->attributes()['viewBox'])[2];

                    $componentRenderNode->options += [
                        'width' => $width,
                        'height' => $this->iconHeight,
                        'path' => FileHelper::FOLDER_SEPARATOR.$this->buildPublicPath().'#'.$key,
                    ];
                }
            } else {
                throw new \Exception('Icon not found : '.$key);
            }
        }
    }

    public function postRender()
    {
        // Create the sprite containing all page icons.
        FileHelper::fileWrite(
            $this->kernel->getProjectDir()
            .FileHelper::FOLDER_SEPARATOR.AssetsService::DIR_PUBLIC
            .$this->buildPublicPath(),
            '<svg xmlns="http://www.w3.org/2000/svg" '.
            '>'.implode($this->renderedIcons).'</svg>'
        );
    }

    public function buildPublicPath(): string
    {
        return AssetsService::DIR_BUILD.self::DIR_BUILD_ICONS
            .$this->adaptiveResponseService->renderPass->pageName
            .FileHelper::FOLDER_SEPARATOR.VariableHelper::PLURAL_ICON.'.'
            .FileHelper::SUFFIX_AGGREGATED.'.'.FileHelper::FILE_EXTENSION_SVG;
    }
}
