<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Service;

use Symfony\Component\HttpKernel\KernelInterface;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\CssAssetTag;
use Wexample\SymfonyDesignSystem\Service\AssetsAggregationService;
use Wexample\SymfonyTesting\Tests\AbstractSymfonyKernelTestCase;

class AssetsAggregationServiceTest extends AbstractSymfonyKernelTestCase
{
    public function testBuildAggregatedTagsWritesAggregatedFile(): void
    {
        $tmp = sys_get_temp_dir() . '/sds-agg-' . uniqid();
        $publicDir = $tmp . '/public';
        mkdir($publicDir . '/build', 0777, true);

        // Source files to aggregate.
        file_put_contents($publicDir . '/build/a.css', '/* a */');
        file_put_contents($publicDir . '/build/b.css', '/* b */');
        file_put_contents($publicDir . '/build/c.css', '/* c */');

        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn($tmp);

        $service = new AssetsAggregationService($kernel);

        $aggTag1 = new CssAssetTag();
        $aggTag1->setPath('build/a.css');
        $aggTag1->setContext(Asset::CONTEXT_LAYOUT);
        $aggTag1->setUsageName('default');
        $aggTag1->setCanAggregate(true);

        $aggTag2 = new CssAssetTag();
        $aggTag2->setPath('build/b.css');
        $aggTag2->setContext(Asset::CONTEXT_LAYOUT);
        $aggTag2->setUsageName('default');
        $aggTag2->setCanAggregate(true);

        $nonAggTag = new CssAssetTag();
        $nonAggTag->setPath('build/c.css');
        $nonAggTag->setContext(Asset::CONTEXT_LAYOUT);
        $nonAggTag->setUsageName('default');
        $nonAggTag->setCanAggregate(false);

        $baseTags = [
            Asset::EXTENSION_CSS => [
                Asset::CONTEXT_LAYOUT => [
                    'default' => [$aggTag1, $aggTag2, $nonAggTag],
                ],
            ],
        ];

        $result = $service->buildAggregatedTags('bundle/view', $baseTags);

        // Aggregated tag exists with hash suffix.
        $aggEntries = $result[Asset::EXTENSION_CSS][Asset::CONTEXT_LAYOUT]['default-agg'] ?? [];
        $this->assertNotEmpty($aggEntries);
        $aggregatedPath = $aggEntries[0]->getPath();
        $this->assertStringStartsWith('build/bundle/view-0.agg.css?', $aggregatedPath);

        // Underlying aggregated file is written (without query string).
        $pathWithoutHash = explode('?', $aggregatedPath)[0];
        $this->assertFileExists($publicDir . '/' . $pathWithoutHash);

        // Non-aggregated tag is still present.
        $defaultEntries = $result[Asset::EXTENSION_CSS][Asset::CONTEXT_LAYOUT]['default'] ?? [];
        $this->assertSame('build/c.css', $defaultEntries[0]->getPath());
    }
}
