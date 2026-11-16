<?php

declare(strict_types=1);

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Twig;

use LogicException;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Wexample\SymfonyDesignSystem\Twig\AbstractTemplateExtension;
use Wexample\SymfonyLoader\Twig\ComponentsExtension;

/**
 * A design-system function drawn from a template the loader did not render
 * names the component it could not draw, instead of a TypeError from deep
 * inside the loader.
 */
class AbstractTemplateExtensionTest extends TestCase
{
    public function testAContextWithoutARenderPassSaysWhichComponent(): void
    {
        $extension = new class ($this->createStub(ComponentsExtension::class)) extends AbstractTemplateExtension {
        };

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('"@WexampleSymfonyDesignSystemBundle/components/badge"');

        $extension->renderComponent(
            $this->createStub(Environment::class),
            ['title' => 'no render pass here'],
            '@WexampleSymfonyDesignSystemBundle/components/badge'
        );
    }
}
