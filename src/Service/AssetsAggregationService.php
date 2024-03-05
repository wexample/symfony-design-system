<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Symfony\Component\HttpKernel\KernelInterface;
use Wexample\SymfonyDesignSystem\Rendering\AssetTag;
use Wexample\SymfonyHelpers\Helper\FileHelper;

class AssetsAggregationService
{
    public const DIR_BUILD = 'build/';

    public const DIR_PUBLIC = 'public/';

    private string $pathProject;

    private string $pathPublic;

    public function __construct(
        KernelInterface $kernel,
    )
    {
        $this->pathProject = $kernel->getProjectDir().'/';
        $this->pathPublic = $this->pathProject.self::DIR_PUBLIC;
    }

    public function buildAggregatedTags(
        string $templateName,
        array $tags,
        string $type
    ): array {
        $aggregated = [];
        /** @var ?AssetTag $aggregationTag */
        $aggregationTag = null;
        $aggregationContent = '';
        $counter = 0;

        /** @var AssetTag $tag */
        foreach ($tags as $tag) {
            if ($tag->canAggregate()) {
                if (!$aggregationTag) {
                    $aggregationTag = new AssetTag();

                    $aggregationTag->setId(
                        $templateName . '-' . $counter
                    );

                    $aggregationTag->setPath(
                        $this->buildAggregatedPathFromPageName(
                            $templateName,
                            $type,
                            $counter,
                        )
                    );

                    $counter++;
                }

                $tagPath = $tag->getPath();
                $aggregationContent .= PHP_EOL.'/* '.$tagPath.' */ '.PHP_EOL
                    .file_get_contents($tagPath);
            } else {
                $this->writeAggregationTag(
                    $aggregationTag,
                    $aggregationContent,
                    $aggregated
                );

                $aggregationTag = null;
                $aggregationContent = '';

                $aggregated[] = $tag;
            }
        }

        $this->writeAggregationTag(
            $aggregationTag,
            $aggregationContent,
            $aggregated
        );

        return $aggregated;
    }

    private function writeAggregationTag(
        ?AssetTag $tag,
        string $body,
        &$tags
    ): void {
        // Null tag says that no file has been read.
        if (is_null($tag)) {
            return;
        }

        $hash = FileHelper::fileWriteAndHash(
            $this->pathPublic.$tag->getPath(),
            $body
        );

        $tag->setPath(
            $tag->getPath() . '?' . $hash
        );

        $tags[] = $tag;
    }

    protected function buildAggregatedPathFromPageName(
        string $templateName,
        string $type,
        int $counter
    ): string {
        return self::DIR_BUILD.implode(
                '/'.$type.'/',
                explode(
                    '::',
                    $templateName
                )
            ).'-'.$counter.'.'.FileHelper::SUFFIX_AGGREGATED.'.'.$type;
    }
}
