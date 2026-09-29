<?php

namespace DominionSolutions\FilamentSeo\Support;

use Illuminate\Support\Str;
use League\CommonMark\CommonMarkConverter;

/**
 * Text helpers for turning authored copy into metadata-safe strings.
 *
 * Meta descriptions have a soft length budget: Google truncates somewhere
 * around 160 characters, and anything past that is wasted authoring effort.
 * These helpers keep that budget honest by normalising whitespace and cutting
 * on a word boundary, so a description never ends mid-word.
 */
final class Text
{
    /**
     * The character budget for a meta description, including the ellipsis.
     */
    public const DESCRIPTION_LENGTH = 160;

    /**
     * Strip markup and collapse whitespace so authored copy can be reused as
     * plain-text metadata.
     */
    public static function plain(?string $value): string
    {
        if (blank($value)) {
            return '';
        }

        $plain = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5);

        return trim((string) preg_replace('/\s+/u', ' ', $plain));
    }

    /**
     * Truncate to a character budget, cutting on a word boundary and appending
     * an ellipsis when anything was removed.
     */
    public static function truncate(?string $value, int $limit): ?string
    {
        $value = trim((string) $value);

        if ($limit <= 0) {
            return null;
        }

        if (blank($value) || mb_strlen($value) <= $limit) {
            return $value === '' ? null : $value;
        }

        // Reserve a character for the ellipsis so the result still fits the
        // budget, which is what makes the budget predictable for callers.
        $truncated = mb_substr($value, 0, $limit - 1);

        // Prefer the last word boundary, but only when it does not throw away
        // most of the budget; a very long first word should still be cut.
        $lastSpace = mb_strrpos($truncated, ' ');

        if ($lastSpace !== false && $lastSpace >= (int) (($limit - 1) * 0.5)) {
            $truncated = mb_substr($truncated, 0, $lastSpace);
        }

        return rtrim($truncated, " \t\n\r\0\x0B.,;:").'…';
    }

    /**
     * Normalise authored copy straight into a meta-description candidate.
     */
    public static function excerpt(?string $value, int $limit = self::DESCRIPTION_LENGTH): ?string
    {
        return self::truncate(self::plain($value), $limit);
    }

    /**
     * The same, for copy authored in Markdown.
     *
     * Renders the Markdown to HTML and then reduces it to plain text, so syntax
     * like `#` or `**bold**` does not leak into a meta description. Falls back to
     * `excerpt()` when no Markdown converter is installed, so the package does
     * not require one.
     */
    public static function fromMarkdown(?string $value, int $limit = self::DESCRIPTION_LENGTH): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (! class_exists(CommonMarkConverter::class)) {
            return self::excerpt($value, $limit);
        }

        return self::truncate(self::plain((string) Str::markdown($value)), $limit);
    }
}
