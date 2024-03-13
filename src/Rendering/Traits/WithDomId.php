<?php

namespace Wexample\SymfonyDesignSystem\Rendering\Traits;

trait WithDomId
{
    private string $domId;

    public function getDomId(): string
    {
        return $this->domId;
    }

    public function setDomId(string $domId): void
    {
        $this->domId = $domId;
    }
}