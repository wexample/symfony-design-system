<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyLoader\Twig\ComponentsExtension;

/**
 * A one-colour drawing — a logo, a mark, an illustration — written into the
 * page rather than linked, so it takes the colour of the text around it: black
 * on a light scheme, white on a dark one, with no second file and no filter. Whatever colour the file was drawn in is
 * handed to `currentColor`; what an editor leaves in it — its own metadata,
 * a fixed size — is dropped, the height being the stylesheet's.
 */
class InlineSvgExtension extends AbstractTemplateExtension
{
    private const EDITOR_NAMESPACES = [
        'http://www.inkscape.org/namespaces/inkscape',
        'http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd',
    ];

    /** @var array<string, string> the markup of each file, read once per request */
    private array $svgs = [];

    public function __construct(
        ComponentsExtension $componentsExtension,
        #[Autowire('%kernel.project_dir%/public')]
        private readonly string $publicDir,
    ) {
        parent::__construct($componentsExtension);
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'inline_svg',
                function (Environment $twig, $context, string $path, array $options = []): string {
                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/inline-svg',
                        [
                            'svg' => $this->monochrome($path),
                            'href' => $options['href'] ?? null,
                            'label' => $options['label'] ?? null,
                            'class' => $options['class'] ?? null,
                            // Red to the left, cyan to the right, as a worn tape
                            // shifts its colours.
                            'chromatic' => $options['chromatic'] ?? false,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }

    /**
     * @param string $path under the public directory, as `asset()` takes it
     */
    public function monochrome(string $path): string
    {
        if (isset($this->svgs[$path])) {
            return $this->svgs[$path];
        }

        $file = $this->publicDir.'/'.ltrim($path, '/');
        $document = new DOMDocument();

        if (! is_file($file) || ! @$document->loadXML((string) file_get_contents($file))) {
            return $this->svgs[$path] = '';
        }

        $svg = $document->documentElement;
        $xpath = new DOMXPath($document);

        // What the editor keeps for itself, and comments.
        foreach (iterator_to_array($xpath->query('//comment() | //*[local-name()="metadata"]')) as $node) {
            $node->parentNode->removeChild($node);
        }

        foreach (iterator_to_array($xpath->query('//*')) as $element) {
            /** @var DOMElement $element */
            if (in_array($element->namespaceURI, self::EDITOR_NAMESPACES, true)) {
                $element->parentNode->removeChild($element);

                continue;
            }

            foreach (iterator_to_array($element->attributes) as $attribute) {
                if (in_array($attribute->namespaceURI, self::EDITOR_NAMESPACES, true)) {
                    $element->removeAttributeNode($attribute);
                }
            }

            foreach (['fill', 'stroke'] as $paint) {
                $value = $element->getAttribute($paint);

                if ($value !== '' && $value !== 'none') {
                    $element->setAttribute($paint, 'currentColor');
                }
            }

            if ($element->hasAttribute('style')) {
                $element->setAttribute('style', preg_replace(
                    ['/\b(fill|stroke)\s*:\s*(?!none\b)[^;]+/', '/-inkscape-[\w-]+\s*:[^;]*;?/'],
                    ['$1:currentColor', ''],
                    $element->getAttribute('style')
                ));
            }
        }

        // The size is the stylesheet's: the viewBox keeps the proportions.
        foreach (['width', 'height', 'id'] as $attribute) {
            $svg->removeAttribute($attribute);
        }

        $svg->setAttribute('class', 'inline-svg--graphic');
        $svg->setAttribute('aria-hidden', 'true');
        $svg->setAttribute('focusable', 'false');

        $markup = $document->saveXML($svg);

        // Declarations of the namespaces just emptied.
        foreach (self::EDITOR_NAMESPACES as $namespace) {
            $markup = preg_replace('/\s+xmlns:\w+="'.preg_quote($namespace, '/').'"/', '', $markup);
        }

        return $this->svgs[$path] = $markup;
    }
}
