<?php

namespace Wexample\SymfonyDesignSystem\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * A file sent through `upload_url()` is whole, checked, and lies in the
 * directory the address was signed for. A listener may take it from there —
 * move it into a library, attach it to something — and say so in the answer
 * the browser gets back (`setData()`), or refuse it (`refuse()`), in which
 * case the file is removed.
 */
final class UploadCompletedEvent extends Event
{
    /** @var array<string, mixed> */
    private array $data = [];

    private ?string $refusal = null;

    private int $refusalStatus = 400;

    public function __construct(
        public readonly string $directory,
        public readonly string $path,
        public readonly string $fileName,
        public readonly Request $request,
    ) {
    }

    /**
     * Added to the JSON answer, beside `complete` and `name`.
     *
     * @param array<string, mixed> $data
     */
    public function setData(array $data): void
    {
        $this->data = $data + $this->data;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function refuse(
        string $reason,
        int $status = 400
    ): void {
        $this->refusal = $reason;
        $this->refusalStatus = $status;
        $this->stopPropagation();
    }

    public function getRefusal(): ?string
    {
        return $this->refusal;
    }

    public function getRefusalStatus(): int
    {
        return $this->refusalStatus;
    }
}
