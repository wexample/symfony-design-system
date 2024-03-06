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
            self::ICONS_LIBRARY_FA => $this->buildIconsListFa(),
            self::ICONS_LIBRARY_MATERIAL => $this->buildIconsListMaterial(),
        ];
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

    public function buildIconsListMaterial(): array
    {
        $output = [];
        $pathFonts = $this->projectDir.'/node_modules/material-design-icons/';

        foreach (scandir($pathFonts) as $item) {
            $pathSvg = $pathFonts.$item.'/svg/production/';
            if ($item[0] !== '.' && is_dir($pathFonts.$item) && file_exists($pathSvg)) {
                foreach (scandir($pathSvg) as $fileIcon) {
                    if ($fileIcon[0] !== '.') {
                        $prefix = 'ic_';
                        $suffix = '_24px.svg';
                        if (str_starts_with($fileIcon, $prefix) && str_ends_with($fileIcon, $suffix)) {
                            $iconName = TextHelper::removePrefix(
                                TextHelper::removeSuffix(
                                    $fileIcon,
                                    $suffix
                                ),
                                $prefix
                            );

                            $output[$iconName] = [
                                'content' => null,
                                'file' => $pathSvg.$fileIcon,
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
                foreach ($icons as $fileIcon) {
                    if ('.' !== $fileIcon[0]) {
                        $iconName = $group.'/'.FileHelper::removeExtension(basename($fileIcon));
                        $output[$iconName] = [
                            'name' => $iconName,
                            'file' => $pathSvg.$group.'/'.$fileIcon,
                            'content' => null,
                        ];
                    }
                }
            }
        }

        return $output;
    }

    /**
     * @throws Exception
     */
    public function icon(
        Environment $twig,
        string $name,
        bool $global = false,
        array $classes = []
    ): string {
        $type = null;

        if (str_contains($name, self::LIBRARY_SEPARATOR)) {
            [$type, $name] = explode(
                self::LIBRARY_SEPARATOR,
                $name
            );
        }

        $default = DomHelper::buildTag('span');

        // Materialize.
        if (self::ICONS_LIBRARY_MATERIAL === $type || (null === $type && isset($this->icons->material[$name]))) {
            return $this->loadIconSvg($this->icons->material, $name, $classes) ?: $default;
        }

        // Font Awesome.
        if (self::ICONS_LIBRARY_FA === $type || (null === $type && isset($this->icons->fa->$name))) {
            return $this->loadIconSvg($this->icons->fa, $name, $classes) ?: $default;
        }

        // Just display tag on error.
        return $default;
    }

    private function loadIconSvg(
        array &$registry,
        string $name,
        array $classes
    ): ?string {
        if (isset($registry[$name])) {
            if (is_null($registry[$name]['content'])) {
                $svgContent = file_get_contents($registry[$name]['file']);

                $dom = new DOMDocument();
                $dom->loadXML($svgContent);
                $tags = $dom->getElementsByTagName('svg');
                if ($tags->length > 0) {
                    $svg = $tags->item(0);

                    $existingClass = $svg->getAttribute('class');
                    $svg->setAttribute(
                        'class',
                        $existingClass.implode(' ',
                            array_merge([
                                'icon',
                            ], $classes)));

                    $content = $dom->saveXML($svg);

                    $registry[$name]['content'] = $content;

                    return $registry[$name]['content'];
                }
            }
        }

        return null;
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
}
