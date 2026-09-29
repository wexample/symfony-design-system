<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Style;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * The design system reads the same both ways: a page in Arabic mirrors it, a
 * page in French does not, and neither asks for a stylesheet of its own. That
 * holds only while every stylesheet speaks of the start and the end of a line
 * rather than its left and right, which is what this checks.
 *
 * What is physical on purpose says so and why, on its line (`// physical:`) or
 * at the head of its file (`// physical-file:`) — a box placed in screen
 * coordinates by a script, a centring that is the same both ways.
 */
class LogicalPropertiesTest extends TestCase
{
    private const PATTERNS = [
        '/\b(margin|padding|border|scroll-margin|scroll-padding)-(left|right)\b/' => 'margin-, padding-, border-inline-start / -end',
        '/^\s*(left|right)\s*:/' => 'inset-inline-start / -end',
        '/text-align:\s*(left|right)\b/' => 'text-align: start / end',
        '/(float|clear):\s*(left|right)\b/' => 'float: inline-start / inline-end',
        '/border-(top|bottom)-(left|right)-radius/' => 'border-start-start-radius and its kin',
        '/translateX\((?![^;]*(direction-sign|-inline\b))(?!0\))/' => 'a translateX multiplied by var(--direction-sign), or a variable named -inline that is',
    ];

    public function testStylesheetsSpeakOfStartAndEnd(): void
    {
        $offences = [];
        $root = dirname(__DIR__, 3).'/assets';
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

        foreach ($files as $file) {
            if ($file->getExtension() !== 'scss') {
                continue;
            }

            $source = (string) file_get_contents($file->getPathname());

            if (str_contains($source, '// physical-file:')) {
                continue;
            }

            foreach (explode("\n", $source) as $index => $line) {
                if (str_contains($line, '// physical') || str_starts_with(ltrim($line), '//')) {
                    continue;
                }

                foreach (self::PATTERNS as $pattern => $instead) {
                    if (preg_match($pattern, $line)) {
                        $offences[] = sprintf(
                            '%s:%d  %s  → %s',
                            substr($file->getPathname(), strlen($root) + 1),
                            $index + 1,
                            trim($line),
                            $instead
                        );
                    }
                }
            }
        }

        $this->assertSame([], $offences, "Physical properties in the design system's stylesheets:\n".implode("\n", $offences));
    }
}
