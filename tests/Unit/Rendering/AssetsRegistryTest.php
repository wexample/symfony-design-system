<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Rendering;

use PHPUnit\Framework\TestCase;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\AssetsRegistry;

class AssetsRegistryTest extends TestCase
{
    public function testLoadManifestErrors(): void
    {
        $tmp = sys_get_temp_dir().'/sds-registry-'.uniqid();
        mkdir($tmp.'/public/build', 0777, true);

        // Invalid JSON.
        file_put_contents($tmp.'/public/build/manifest.json', '{invalid json');

        $this->expectException(\RuntimeException::class);
        new AssetsRegistry($tmp);
    }

    public function testManifestAccessorsAndRealPath(): void
    {
        $tmp = sys_get_temp_dir().'/sds-registry-'.uniqid();
        mkdir($tmp.'/public/build', 0777, true);

        file_put_contents($tmp.'/public/build/main.js', '//');
        file_put_contents($tmp.'/public/build/manifest.json', json_encode([
            'entry.js' => 'build/main.js',
        ]));

        $registry = new AssetsRegistry($tmp);

        $this->assertTrue($registry->assetExists('entry.js'));
        $this->assertSame('build/main.js', $registry->getBuiltPath('entry.js'));
        $this->assertNotNull($registry->getRealPath('entry.js'));
    }

    public function testAddAssetToArrayAndJsonSerialize(): void
    {
        $tmp = sys_get_temp_dir().'/sds-registry-'.uniqid();
        mkdir($tmp.'/public/build', 0777, true);
        file_put_contents($tmp.'/public/build/manifest.json', '{}');

        $registry = new AssetsRegistry($tmp);
        $asset = new Asset('build/app.css', 'view', 'default', Asset::CONTEXT_LAYOUT);

        $registry->addAsset($asset);

        $array = $registry->toArray();
        $this->assertArrayHasKey($asset->getType(), $array);
        $this->assertArrayHasKey($asset->getView(), $array[$asset->getType()]);

        $json = $registry->jsonSerialize();
        $this->assertEquals($array, $json);
    }
}
