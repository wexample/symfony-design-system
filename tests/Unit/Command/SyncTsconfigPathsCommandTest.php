<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Wexample\SymfonyDesignSystem\Command\SyncTsconfigPathsCommand;
use Wexample\SymfonyDesignSystem\Service\Encore\TsconfigPathsSynchronizer;
use Wexample\SymfonyHelpers\Service\BundleService;
use PHPUnit\Framework\TestCase;

class SyncTsconfigPathsCommandTest extends TestCase
{
    public function testExecuteCallsSynchronizerWithDefaults(): void
    {
        $synchronizer = $this->createMock(TsconfigPathsSynchronizer::class);
        $synchronizer
            ->expects($this->once())
            ->method('sync')
            ->with('tsconfig.json', 'assets/encore.manifest.json');

        $command = new SyncTsconfigPathsCommand(
            $this->createStub(BundleService::class),
            $synchronizer
        );

        $tester = new CommandTester($command);
        $status = $tester->execute([]);

        $this->assertSame(\Symfony\Component\Console\Command\Command::SUCCESS, $status);
        $this->assertStringContainsString('tsconfig paths updated', $tester->getDisplay());
    }

    public function testExecuteCallsSynchronizerWithCustomPaths(): void
    {
        $synchronizer = $this->createMock(TsconfigPathsSynchronizer::class);
        $synchronizer
            ->expects($this->once())
            ->method('sync')
            ->with('custom-tsconfig.json', 'custom-manifest.json');

        $command = new SyncTsconfigPathsCommand(
            $this->createStub(BundleService::class),
            $synchronizer
        );

        $tester = new CommandTester($command);
        $status = $tester->execute([
            '--tsconfig' => 'custom-tsconfig.json',
            '--manifest' => 'custom-manifest.json',
        ]);

        $this->assertSame(\Symfony\Component\Console\Command\Command::SUCCESS, $status);
    }
}
