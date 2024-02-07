<?php

namespace Wexample\SymfonyDesignSystem\Helper;

use Wexample\SymfonyHelpers\Helper\VariableHelper;
use function implode;
use function is_null;

class DomHelper
{
    public const TAG_LINK = 'link';

    public const TAG_ALLOWS_AUTO_CLOSING = [
        'div' => false,
        'span' => false,
    ];

    public static function buildTagAttributes(array $attributes): string
    {
        $output = [];
        $attributes = $attributes ?: [];

        foreach ($attributes as $key => $value) {
            if (null !== $value) {
                $output[] = $key.'="'.$value.'"';
            }
        }

        return implode(VariableHelper::_SPACE, $output);
    }

    public static function buildTag(
        string $tagName,
        array $attributes,
        string $body = '',
        bool $allowSingleTag = null
    ): string {
        $output = '<'.$tagName;

        $outputAttributes = static::buildTagAttributes($attributes);
        $output .= $outputAttributes ? ' '.$outputAttributes : '';

        if (is_null($allowSingleTag)) {
            $allowSingleTag = static::TAG_ALLOWS_AUTO_CLOSING[$tagName] ?? false;
        }

        if ($allowSingleTag && !$body) {
            $output .= '/>';
        } else {
            $output .= '>'.$body.'</'.$tagName.'>';
        }

        return $output;
    }
}
