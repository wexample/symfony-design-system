<?php

namespace Wexample\SymfonyDesignSystem\Tests;

use Wexample\SymfonyHelpers\Tests\Class\AbstractApplicationTestCase;

abstract class AbstractDesignSystemTestCase extends AbstractApplicationTestCase
{
    protected function getPageLayoutData(string $content = null): array
    {
        $matches = [];
        preg_match(
            '/layoutRenderData = ([.\S\s\n]*);(\s*)<\/script>/',
            $content ?? $this->content(),
            $matches,
        );

        return json_decode($matches[1], JSON_OBJECT_AS_ARRAY);
    }
}
