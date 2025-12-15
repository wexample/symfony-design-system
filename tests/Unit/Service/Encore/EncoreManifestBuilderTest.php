<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Service\Encore;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Wexample\SymfonyDesignSystem\Service\Encore\EncoreManifestBuilder;
use PHPUnit\Framework\TestCase;

class EncoreManifestBuilderTest extends TestCase
{
    private string $tmpDir;
    private Filesystem $fs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fs = new Filesystem();
        $this->tmpDir = sys_get_temp_dir().'/sds-encore-'.uniqid();
        $this->fs->mkdir($this->tmpDir);
    }

    protected function tearDown(): void
    {
        $this->fs->remove($this->tmpDir);
        parent::tearDown();
    }

    public function testBuildWithNoFrontPathsReturnsEmpty(): void
    {
        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn($this->tmpDir);

        $builder = new EncoreManifestBuilder($kernel, new ParameterBag([]));

        $manifest = $builder->build();

        $this->assertSame(0, $manifest['frontCount']);
        $this->assertSame([], $manifest['fronts']);
    }

    public function testBuildBundleTagDefaultsToFrontForNumericKey(): void
    {
        $frontDir = $this->tmpDir.'/front-num';
        $this->fs->mkdir($frontDir);

        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn($this->tmpDir);

        $builder = new EncoreManifestBuilder(
            $kernel,
            new ParameterBag([
                'design_system_packages_front_paths' => [
                    'app' => [
                        0 => $frontDir,
                    ],
                ],
            ])
        );

        $manifest = $builder->build();

        $this->assertSame('@front', $manifest['fronts'][0]['bundle']);
    }

    public function testBuildGeneratesEntriesForFrontPaths(): void
    {
        $frontDir = $this->tmpDir.'/front';
        $this->fs->mkdir([
            $frontDir.'/layouts',
            $frontDir.'/pages',
            $frontDir.'/components',
            $frontDir.'/forms',
            $frontDir.'/vue',
        ]);

        // CSS
        file_put_contents($frontDir.'/styles.scss', '/* css */');
        // JS main (allowed dir)
        file_put_contents($frontDir.'/layouts/main.js', '// main');
        // Uppercase class file should be ignored for main
        file_put_contents($frontDir.'/layouts/MainClass.js', '// ignore');
        // Pages/components/forms/vue
        file_put_contents($frontDir.'/pages/home.ts', '// page');
        file_put_contents($frontDir.'/components/button.ts', '// component');
        file_put_contents($frontDir.'/forms/form.ts', '// form');
        file_put_contents($frontDir.'/vue/foo.vue', '<template></template>');
        // Ignored underscore file
        file_put_contents($frontDir.'/components/_ignore.ts', '// ignore');

        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn($this->tmpDir);

        $params = new ParameterBag([
            'design_system_packages_front_paths' => [
                'app' => [
                    'app' => $frontDir,
                ],
            ],
        ]);

        $builder = new EncoreManifestBuilder($kernel, $params);

        $manifest = $builder->build();

        $this->assertSame(1, $manifest['frontCount']);
        $this->assertSame('./front/', $manifest['aliases']['app']);
        $this->assertSame(1, count($manifest['fronts']));

        $entries = $manifest['entries'];
        $this->assertNotEmpty($entries['css']);
        $this->assertNotEmpty($entries['js']['main']);
        $this->assertNotEmpty($entries['js']['pages']);
        $this->assertNotEmpty($entries['js']['components']);
        $this->assertNotEmpty($entries['js']['forms']);
        $this->assertNotEmpty($entries['js']['vue']);

        $css = $entries['css'][0];
        $this->assertSame('@AppBundle/css/styles', $css['output']);

        $main = $entries['js']['main'][0];
        $this->assertSame('main', $main['category']);
        $this->assertSame('@AppBundle/js/layouts/main', $main['output']);

        $pageWrapper = $entries['js']['pages'][0]['wrapper'] ?? null;
        $this->assertNotNull($pageWrapper);
        $this->assertSame('pages', $pageWrapper['type']);
        $this->assertSame('@AppBundle/pages/home', $pageWrapper['className']);

        // Ensure ignored file is not present
        $componentPaths = array_column($entries['js']['components'], 'relative');
        $this->assertNotContains('components/_ignore.ts', $componentPaths);
    }

    public function testBuildProjectRelativePathUsesVendorSymlink(): void
    {
        $projectDir = $this->tmpDir.'/project';
        $vendorDir = $projectDir.'/vendor';
        $targetDir = $this->tmpDir.'/linked/pkg/';
        $this->fs->mkdir($targetDir);
        $this->fs->mkdir($vendorDir);

        $filePath = $targetDir.'file.js';
        file_put_contents($filePath, '//');

        // Create symlink inside vendor pointing to targetDir.
        $vendorSymlink = $vendorDir.'/pkg';
        $this->fs->symlink($targetDir, $vendorSymlink);

        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn($projectDir);

        $builder = new EncoreManifestBuilder($kernel, new ParameterBag([]));

        $relative = $this->invokePrivate($builder, 'buildProjectRelativePath', [$filePath]);

        $this->assertSame('./vendor/pkg/file.js', $relative);
    }

    public function testEnsureTrailingSeparatorIfDirectoryAppendsSlash(): void
    {
        $dir = $this->tmpDir.'/dir-without-slash';
        $this->fs->mkdir($dir);

        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn($this->tmpDir);
        $builder = new EncoreManifestBuilder($kernel, new ParameterBag([]));

        $result = $this->invokePrivate($builder, 'ensureTrailingSeparatorIfDirectory', [$dir]);

        $this->assertStringEndsWith(DIRECTORY_SEPARATOR, $result);
    }

    public function testBuildProjectRelativePathReturnsAbsoluteWhenOutside(): void
    {
        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn($this->tmpDir.'/project-no-vendor');

        $builder = new EncoreManifestBuilder($kernel, new ParameterBag([]));

        $absolute = $this->tmpDir.'/outside/file.js';
        $this->fs->dumpFile($absolute, '//');

        $relative = $this->invokePrivate($builder, 'buildProjectRelativePath', [$absolute]);

        $this->assertSame($absolute, $relative);
    }

    public function testVendorSymlinkCacheSkipsNonLinksAndBrokenLinks(): void
    {
        $projectDir = $this->tmpDir.'/proj2';
        $vendorDir = $projectDir.'/vendor';
        $this->fs->mkdir($vendorDir);

        // Non-link file
        $this->fs->dumpFile($vendorDir.'/file.txt', 'x');
        // Broken symlink
        @symlink($projectDir.'/missing', $vendorDir.'/broken');

        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn($projectDir);

        $builder = new EncoreManifestBuilder($kernel, new ParameterBag([]));

        $cache = $this->invokePrivate($builder, 'getVendorSymlinkCache', []);
        // Second call hits cached branch.
        $cache = $this->invokePrivate($builder, 'getVendorSymlinkCache', []);

        $this->assertSame([], $cache);
    }

    public function testMergeEntriesCreatesMissingCategories(): void
    {
        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn($this->tmpDir);
        $builder = new EncoreManifestBuilder($kernel, new ParameterBag([]));

        $target = [
            'css' => [],
            'js' => ['main' => []],
        ];

        $assets = [
            'css' => [],
            'js' => [
                'newcat' => [['relative' => 'file.js']],
            ],
        ];

        $this->invokePrivate($builder, 'mergeEntries', [&$target, $assets]);

        $this->assertArrayHasKey('newcat', $target['js']);
        $this->assertSame('file.js', $target['js']['newcat'][0]['relative']);
    }

    /**
     * @param object $object
     * @param string $method
     * @param array<int,mixed> $args
     */
    private function invokePrivate(object $object, string $method, array $args)
    {
        $refMethod = new \ReflectionMethod($object, $method);
        $refMethod->setAccessible(true);

        return $refMethod->invokeArgs($object, $args);
    }
}
