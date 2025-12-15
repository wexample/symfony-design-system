<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Wexample\SymfonyDesignSystem\Command\GenerateEncoreManifestCommand;
use Wexample\SymfonyDesignSystem\Service\Encore\EncoreManifestBuilder;
use Wexample\SymfonyDesignSystem\Service\Encore\TsconfigPathsSynchronizer;
use Wexample\SymfonyHelpers\Service\BundleService;
use PHPUnit\Framework\TestCase;

class GenerateEncoreManifestCommandTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tmpDir = sys_get_temp_dir().'/sds-manifest-'.uniqid();
        (new Filesystem())->mkdir($this->tmpDir);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->tmpDir);
        parent::tearDown();
    }

    public function testExecuteWritesManifestAndSyncsTsconfig(): void
    {
        $manifest = [
            'frontCount' => 1,
            'fronts' => ['foo'],
            'version' => '1.2.3',
        ];

        $builder = $this->createMock(EncoreManifestBuilder::class);
        $builder->expects($this->once())->method('build')->willReturn($manifest);

        $tsconfig = $this->createMock(TsconfigPathsSynchronizer::class);
        $tsconfig->expects($this->once())->method('sync')
            ->with(
                $this->anything(),
                $this->stringContains('assets/encore.manifest.json')
            );

        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn($this->tmpDir);

        $command = new GenerateEncoreManifestCommand(
            $this->createStub(BundleService::class),
            $builder,
            $tsconfig,
            $kernel,
            new Filesystem()
        );

        $tester = new CommandTester($command);
        $tester->execute([]);

        $manifestPath = $this->tmpDir.'/assets/encore.manifest.json';
        $this->assertFileExists($manifestPath);
        $this->assertSame($manifest, json_decode(file_get_contents($manifestPath), true));
    }

    public function testExecuteWithCustomOutputAndNoSync(): void
    {
        $manifest = ['fronts' => [], 'version' => 'x'];
        $customPath = $this->tmpDir.'/custom/manifest.json';

        $builder = $this->createStub(EncoreManifestBuilder::class);
        $builder->method('build')->willReturn($manifest);

        $tsconfig = $this->createMock(TsconfigPathsSynchronizer::class);
        $tsconfig->expects($this->never())->method('sync');

        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn($this->tmpDir);

        $command = new GenerateEncoreManifestCommand(
            $this->createStub(BundleService::class),
            $builder,
            $tsconfig,
            $kernel,
            new Filesystem()
        );

        $tester = new CommandTester($command);
        $tester->execute([
            '--output' => $customPath,
            '--sync-tsconfig' => false,
            '--pretty' => false,
        ]);

        $this->assertFileExists($customPath);
        $this->assertSame($manifest, json_decode(file_get_contents($customPath), true));
    }

    public function testExecuteFailsWhenJsonWriteFails(): void
    {
        // json_encode will return false with NAN values.
        $manifest = ['fronts' => [NAN]];
        $outputPath = $this->tmpDir.'/assets/manifest.json';

        $builder = $this->createStub(EncoreManifestBuilder::class);
        $builder->method('build')->willReturn($manifest);

        $tsconfig = $this->createStub(TsconfigPathsSynchronizer::class);

        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn($this->tmpDir);

        $command = new GenerateEncoreManifestCommand(
            $this->createStub(BundleService::class),
            $builder,
            $tsconfig,
            $kernel,
            new Filesystem()
        );

        $tester = new CommandTester($command);
        $status = $tester->execute(['--output' => $outputPath]);

        $this->assertSame(\Symfony\Component\Console\Command\Command::FAILURE, $status);
        $this->assertFileDoesNotExist($outputPath);
    }

    public function testExecuteWithAbsolutePathOutsideProjectDisplaysAbsolute(): void
    {
        $projectDir = $this->tmpDir.'/projectA';
        $outsideDir = $this->tmpDir.'/projectB';
        (new Filesystem())->mkdir([$projectDir, $outsideDir]);

        $manifest = ['fronts' => [], 'version' => 'x'];
        $outputPath = $outsideDir.'/custom-manifest.json';

        $builder = $this->createStub(EncoreManifestBuilder::class);
        $builder->method('build')->willReturn($manifest);

        $tsconfig = $this->createMock(TsconfigPathsSynchronizer::class);
        $tsconfig->expects($this->never())->method('sync');

        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn($projectDir);

        $command = new GenerateEncoreManifestCommand(
            $this->createStub(BundleService::class),
            $builder,
            $tsconfig,
            $kernel,
            new Filesystem()
        );

        $tester = new CommandTester($command);
        $tester->execute([
            '--output' => $outputPath,
            '--sync-tsconfig' => false,
        ]);

        $this->assertFileExists($outputPath);
        $this->assertStringContainsString($outputPath, $tester->getDisplay());
    }
}
