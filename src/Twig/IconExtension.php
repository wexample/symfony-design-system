<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use DOMDocument;
use Exception;
use stdClass;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Helper\DomHelper;
use Wexample\SymfonyHelpers\Helper\FileHelper;
use Wexample\SymfonyHelpers\Helper\TextHelper;
use Wexample\SymfonyHelpers\Helper\VariableHelper;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;

class IconExtension extends AbstractExtension
{
    /**
     * @var string
     */
    private const ICONS_LIBRARY_FA = 'fa';

    /**
     * @var string
     */
    private const ICONS_LIBRARY_MATERIAL = 'material';

    /**
     * @var string
     */
    public const LIBRARY_SEPARATOR = ':';

    protected stdClass $icons;

    private string $projectDir;

    public function __construct(
        KernelInterface $kernel,
        protected ComponentsExtension $componentsExtension
    ) {
        $this->projectDir = $kernel->getProjectDir();
        $this->icons = (object) [
            self::ICONS_LIBRARY_MATERIAL => $this->buildIconsListMaterial(),
        ];
    }

    public function buildIconsListMaterial(): array
    {
        $output = [];
        $pathFonts = $this->projectDir.'/node_modules/material-design-icons/';

        foreach (scandir($pathFonts) as $item) {
            $svgDir = $pathFonts.$item.'/svg/production/';
            if ($item[0] !== '.' && is_dir($pathFonts.$item) && file_exists($svgDir)) {
                foreach (scandir($svgDir) as $iconFile) {
                    if ($iconFile[0] !== '.') {
                        $prefix = 'ic_';
                        $suffix = '_24px.svg';
                        if (str_starts_with($iconFile, $prefix) && str_ends_with($iconFile, $suffix)) {
                            $iconName = TextHelper::removePrefix(
                                TextHelper::removeSuffix(
                                    $iconFile,
                                    $suffix
                                ),
                                $prefix
                            );

                            $output[$iconName] = [
                                'content' => null,
                                'file' => $svgDir.$iconFile,
                                'name' => $iconName,
                            ];
                        }
                    }
                }
            }
        }

        return $output;
    }

    public function buildIconsListFa(): array
    {
        $pathSvg = $this->projectDir.'/node_modules/@fortawesome/fontawesome-free/svgs/';
        $groups = scandir($pathSvg);
        $output = [];

        foreach ($groups as $group) {
            if ('.' !== $group[0]) {
                $icons = scandir($pathSvg.$group);
                foreach ($icons as $icon) {
                    if ('.' !== $icon[0]) {
                        $iconName = FileHelper::removeExtension(basename($icon));
                        $output[$group][$iconName] = $iconName;
                    }
                }
            }
        }

        return $output;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                VariableHelper::ICON,
                [
                    $this,
                    VariableHelper::ICON,
                ],
                [
                    self::FUNCTION_OPTION_IS_SAFE => self::FUNCTION_OPTION_IS_SAFE_VALUE_HTML,
                    self::FUNCTION_OPTION_NEEDS_ENVIRONMENT => true,
                ]
            ),
            new TwigFunction(
                'icon_list',
                [
                    $this,
                    'iconList',
                ]
            ),
        ];
    }

    /**
     * @throws Exception
     */
    public function icon(
        Environment $twig,
        string $name,
        string $class = '',
        string $tagName = 'i'
    ): string {
        $type = null;

        if (str_contains($name, self::LIBRARY_SEPARATOR)) {
            [$type, $name] = explode(
                self::LIBRARY_SEPARATOR,
                $name
            );
        }

        $class .= ' icon ';
        // Materialize.
        if (self::ICONS_LIBRARY_MATERIAL === $type || (null === $type && isset($this->icons->material[$name]))) {
            $default = DomHelper::buildTag('span');

            if (isset($this->icons->material[$name])) {
                if (is_null($this->icons->material[$name]['content'])) {
                    $svgContent = file_get_contents($this->icons->material[$name]['file']);

                    $dom = new DOMDocument();
                    $dom->loadXML($svgContent);
                    $tags = $dom->getElementsByTagName('svg');
                    if ($tags->length > 0) {
                        $svg = $tags->item(0);

                        $existingClass = $svg->getAttribute('class');
                        $newClass = $existingClass ? $existingClass.' mdc-button__icon' : 'mdc-button__icon';
                        $svg->setAttribute('class', $newClass);

                        $content = $dom->saveXML($svg);
                    } else {
                        $content = $default;
                    }

                    $this->icons->material[$name]['content'] = $content;
                }

                return $this->icons->material[$name]['content'];
            }

            return $default;
        }

        // Just display tag on error.
        return DomHelper::buildTag($tagName, [
            VariableHelper::CLASS_VAR => $class,
        ]);
    }

    /**
     * @throws Exception
     */
    public function iconList(
        string $type
    ): array {
        if ($type === self::ICONS_LIBRARY_FA) {
            return $this->buildIconsListFa();
        } elseif ($type === self::ICONS_LIBRARY_MATERIAL) {
            return $this->buildIconsListMaterial();
        }

        return [];
    }

    private function findFaIconGroup(string $name): ?string
    {
        foreach ($this->icons->fa as $group => $icons) {
            if (isset($icons[$name])) {
                return $group;
            }
        }

        return null;
    }
}
