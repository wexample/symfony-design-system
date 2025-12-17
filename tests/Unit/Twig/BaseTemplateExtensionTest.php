<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Twig;

use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Twig\BaseTemplateExtension;
use Wexample\SymfonyTranslations\Translation\Translator;

class BaseTemplateExtensionTest extends TestCase
{
    public function testFunctionsAndTitleRendering(): void
    {
        $translator = $this->createStub(Translator::class);
        $translator->method('trans')->willReturnMap([
            [BaseTemplateExtension::DEFAULT_LAYOUT_TITLE_TRANSLATION_KEY, [], null, null, 'layout'],
            [BaseTemplateExtension::DEFAULT_APP_TITLE_TRANSLATION_KEY, [], null, null, 'app'],
        ]);

        $extension = new BaseTemplateExtension($translator);

        $functions = $extension->getFunctions();
        $names = array_map(static fn (TwigFunction $f) => $f->getName(), $functions);
        $this->assertContains('base_template_render_title', $names);

        // Default translations
        $this->assertSame('layout | app', $extension->baseTemplateRenderTitle());

        // Override layout only
        $this->assertSame('custom | app', $extension->baseTemplateRenderTitle('custom'));

        // Override app only
        $this->assertSame('layout | customApp', $extension->baseTemplateRenderTitle(null, [], 'customApp'));

        // Empty pieces trimmed out
        $this->assertSame('custom', $extension->baseTemplateRenderTitle('custom', [], ' '));
    }
}
