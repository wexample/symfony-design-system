<?php

namespace Wexample\SymfonyDesignSystem\Chat\SlashCommand;

use LogicException;
use ReflectionClass;
use Wexample\SymfonyDesignSystem\Chat\SlashCommand\Attribute\AsSlashCommand;

/**
 * Every slash command declared in the app, indexed by name.
 *
 * A command is found by the attribute it carries, so the declaration lives in
 * one place and reaches both sides from there: the browser is told what exists
 * and this is what runs the ones that need the server.
 */
class SlashCommandRegistry
{
    final public const string TAG = 'wexample.symfony_design_system.slash_command';

    /**
     * @var array<string, AsSlashCommand>
     */
    private array $attributes = [];

    /**
     * @var array<string, callable>
     */
    private array $commands = [];

    /**
     * @param iterable<object> $commands services tagged as slash commands
     */
    public function __construct(iterable $commands)
    {
        foreach ($commands as $command) {
            $attribute = self::readAttribute($command);

            $this->attributes[$attribute->name] = $attribute;
            $this->commands[$attribute->name] = $command;
        }
    }

    /**
     * What the browser is told about the commands a given chat offers.
     */
    public function getDescriptors(string $group): array
    {
        $descriptors = [];

        foreach ($this->attributes as $name => $attribute) {
            if ($attribute->group === $group) {
                $descriptors[] = [
                    'name' => $name,
                    'description' => $attribute->description,
                    'usage' => $attribute->usage,
                    'frontOnly' => $attribute->frontOnly,
                    'handler' => $attribute->handler,
                ];
            }
        }

        return $descriptors;
    }

    /**
     * Runs whatever command the composed message holds.
     *
     * @return bool false when the message holds no command the group knows,
     *              which is how an unknown slash goes back to being plain text
     */
    public function run(
        string $group,
        string $content,
        array $params = []
    ): bool {
        $parsed = self::parse($content);

        if (! $parsed) {
            return false;
        }

        [$name, $text] = $parsed;
        $attribute = $this->attributes[$name] ?? null;

        if (! $attribute || $attribute->group !== $group || $attribute->frontOnly) {
            return false;
        }

        ($this->commands[$name])(
            new SlashCommandContext($text, $params)
        );

        return true;
    }

    /**
     * A command stands either first or last in the trimmed message, and is
     * separated from the text by a space. A slash inside a sentence is text.
     *
     * The browser parses the same way, for the sole purpose of knowing which
     * commands it must not send.
     *
     * @return array{0: string, 1: string}|null the name, then the text left around it
     */
    public static function parse(string $content): ?array
    {
        $content = trim($content);

        if (preg_match('#^/([\w-]+)(?:\s+(.+))?$#s', $content, $matches)) {
            return [$matches[1], trim($matches[2] ?? '')];
        }

        if (preg_match('#^(.+?)\s+/([\w-]+)$#s', $content, $matches)) {
            return [$matches[2], trim($matches[1])];
        }

        return null;
    }

    private static function readAttribute(object $command): AsSlashCommand
    {
        $attributes = (new ReflectionClass($command))->getAttributes(AsSlashCommand::class);

        if (! $attributes) {
            throw new LogicException($command::class.' is tagged as a slash command but carries no '.AsSlashCommand::class.' attribute.');
        }

        return $attributes[0]->newInstance();
    }
}
