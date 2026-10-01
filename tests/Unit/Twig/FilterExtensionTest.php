<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Twig;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Wexample\SymfonyDesignSystem\Twig\FilterExtension;
use Wexample\SymfonyTranslations\Translation\Translator;

/**
 * The filters of a server table read and written in the page's query: at its
 * top, or under the table's `query_key`, another table's filters untouched.
 */
class FilterExtensionTest extends TestCase
{
    private const array OWNER = ['key' => 'owner', 'label' => 'Owner', 'multiple' => true, 'options' => []];

    private function extension(string $uri): FilterExtension
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create($uri));

        return new FilterExtension($requestStack, $this->createStub(Translator::class));
    }

    public function testWithoutKey(): void
    {
        $extension = $this->extension('/list?owner[0]=a&page=2');

        $this->assertSame(['a'], $extension->filterSelected('owner'));
        $this->assertSame(
            '/list?'.http_build_query(['owner' => ['a', 'b']]),
            $extension->filterToggleUrl(self::OWNER, 'b')
        );
        $this->assertSame('/list', $extension->filterClearUrl('owner'));
    }

    public function testUnderAKeyTheOtherTableKeepsItsFilters(): void
    {
        $extension = $this->extension('/list?'.http_build_query([
            'patients' => ['owner' => ['a'], 'page' => 2],
            'users' => ['owner' => ['z']],
        ]));

        $this->assertSame(['a'], $extension->filterSelected('owner', 'patients'));
        $this->assertSame(['z'], $extension->filterSelected('owner', 'users'));
        $this->assertSame([], $extension->filterSelected('owner'));
        $this->assertSame(
            '/list?'.http_build_query(['patients' => ['owner' => ['a', 'b']], 'users' => ['owner' => ['z']]]),
            $extension->filterToggleUrl(self::OWNER, 'b', 'patients')
        );
        $this->assertSame(
            '/list?'.http_build_query(['users' => ['owner' => ['z']]]),
            $extension->filterClearUrl('owner', 'patients')
        );
    }
}
