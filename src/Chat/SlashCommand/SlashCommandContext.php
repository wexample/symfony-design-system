<?php

namespace Wexample\SymfonyDesignSystem\Chat\SlashCommand;

/**
 * What a command is given when it runs: the text left around it in the
 * composer, and what the chat it was typed in already holds — the room, the
 * thread, whatever the endpoint had at hand.
 */
class SlashCommandContext
{
    public function __construct(
        public readonly string $text,
        public readonly array $params = [],
    ) {
    }

    public function get(string $key): mixed
    {
        return $this->params[$key] ?? null;
    }
}
