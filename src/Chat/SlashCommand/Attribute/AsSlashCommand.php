<?php

namespace Wexample\SymfonyDesignSystem\Chat\SlashCommand\Attribute;

use Attribute;

/**
 * Declares a class as a slash command of a chat.
 *
 * Everything the front needs to know about a command is written here, next to
 * the code that runs it, and travels to the browser as it stands.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class AsSlashCommand
{
    public function __construct(
        /**
         * What is typed after the slash.
         */
        public readonly string $name,
        /**
         * Which chats offer this command. A chat asks for one group and gets
         * nothing else, so two chats of the same app do not share a vocabulary
         * by accident.
         */
        public readonly string $group,
        public readonly ?string $description = null,
        public readonly ?string $usage = null,
        /**
         * True when running the command changes nothing on the server. The
         * class then only carries the declaration, and `handler` names the one
         * that does the work in the browser.
         */
        public readonly bool $frontOnly = false,
        /**
         * Asset name of the front class running the command, for a front-only
         * one.
         */
        public readonly ?string $handler = null,
    ) {
    }
}
