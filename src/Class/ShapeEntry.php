<?php

namespace Wexample\SymfonyDesignSystem\Class;

/**
 * One shape: a style with no renderer, and what it is for.
 *
 * It carries no format and no stance, and that is the whole difference with an
 * element. A shape is drawn by nobody — the caller writes the markup, puts the
 * class on it, and `@use`s the stylesheet from its own — so asking it for a
 * template or a vue twin is asking a question it has no way to answer. It is
 * listed apart for exactly that reason.
 *
 * The description is read from the head of the stylesheet rather than declared
 * beside it: one file, no second place to drift, and the sentence sits where
 * someone editing the shape will see it.
 */
class ShapeEntry
{
    public function __construct(
        public readonly string $key,
        public readonly string $source,
        public readonly string $path,
        public readonly ?string $description = null,
    ) {
    }

    public static function buildId(
        string $source,
        string $key
    ): string {
        return $source . ':' . $key;
    }

    public function getId(): string
    {
        return self::buildId($this->source, $this->key);
    }

    public function getShortName(): string
    {
        $segments = explode('/', $this->key);

        return end($segments);
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'source' => $this->source,
            'path' => $this->path,
            'description' => $this->description,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['key'],
            $data['source'],
            $data['path'],
            $data['description'] ?? null,
        );
    }
}
