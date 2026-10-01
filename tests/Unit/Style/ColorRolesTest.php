<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Style;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Colour reaches a component through roles and scales, never through the
 * levels the system drew with before (`--color-invert-14`, `--color-error-23`,
 * `--color-info-fill`). Those are gone: a stylesheet still reading one reads
 * nothing, and the page shows it as a missing colour nobody can trace back.
 *
 * A palette is twelve tones per scale, given for both faces
 * (css/mixins/palette); a role is a place on a scale (css/mixins/roles).
 */
class ColorRolesTest extends TestCase
{
    private const PATTERN = '/--color-(invert|default|info|success|warning|error|assist|cat|primary|palette|danger)\b[\w-]*/';

    public function testStylesheetsReadRolesAndScales(): void
    {
        $offences = [];
        $root = dirname(__DIR__, 3).'/assets';
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

        foreach ($files as $file) {
            if (! in_array($file->getExtension(), ['scss', 'vue', 'twig', 'ts'], true)) {
                continue;
            }

            foreach (explode("\n", (string) file_get_contents($file->getPathname())) as $index => $line) {
                if (preg_match(self::PATTERN, $line, $match)) {
                    $offences[] = sprintf(
                        '%s:%d  %s',
                        substr($file->getPathname(), strlen($root) + 1),
                        $index + 1,
                        $match[0]
                    );
                }
            }
        }

        $this->assertSame([], $offences, "Colour tokens of the former system:\n".implode("\n", $offences));
    }
}
