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
    }
}
