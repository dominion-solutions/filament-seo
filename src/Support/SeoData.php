<?php

namespace DominionSolutions\FilamentSeo\Support;

/**
 * The metadata for a single page or record.
 *
 * This is the package's core value object and deliberately knows nothing about
 * Filament, Eloquent or HTTP rendering: everything else in the package is a way
 * of producing one of these, or a way of turning one into markup.
 *
 * Values are normalised on construction — trimmed, blank strings collapsed to
 * `null`, and the description held to the character budget — so anything
 * rendering a `SeoData` can trust it.
 */
final readonly class SeoData
{
    public const ROBOTS_INDEX_FOLLOW = 'index, follow';

    public const ROBOTS_NOINDEX_FOLLOW = 'noindex, follow';

    public const ROBOTS_NOINDEX_NOFOLLOW = 'noindex, nofollow';

    public const TYPE_WEBSITE = 'website';

    public const TYPE_ARTICLE = 'article';

    public const TYPE_PROFILE = 'profile';

    public ?string $title;

    public ?string $description;

    public ?string $imageUrl;

    public ?string $imageAlt;

    public ?string $canonicalUrl;

    public string $robots;

    public string $type;

    public ?string $siteName;

    public function __construct(
        ?string $title = null,
        ?string $description = null,
        ?string $imageUrl = null,
        ?string $imageAlt = null,
        ?string $canonicalUrl = null,
        ?string $robots = null,
        ?string $type = null,
        ?string $siteName = null,
        int $descriptionLength = Text::DESCRIPTION_LENGTH,
    ) {
        $this->title = self::clean($title);
        $this->description = Text::truncate(self::clean($description), $descriptionLength);
        $this->imageUrl = self::clean($imageUrl);
        $this->imageAlt = self::clean($imageAlt);
        $this->canonicalUrl = self::clean($canonicalUrl);
        $this->robots = self::clean($robots) ?? self::ROBOTS_INDEX_FOLLOW;
        $this->type = self::clean($type) ?? self::TYPE_WEBSITE;
        $this->siteName = self::clean($siteName);
    }

    /**
     * Named constructor for readability at call sites.
     *
     * @param  array<string, mixed>  $overrides
     */
    public static function make(array $overrides = []): self
    {
        return new self(...$overrides);
    }

    /**
     * Return a copy with the given non-null values applied.
     *
     * Used to layer a record's authored metadata over generated fallbacks:
     * pass only the values that were actually filled in and the rest of the
     * metadata is preserved.
     *
     * @param  array<string, mixed>  $overrides
     */
    public function merge(array $overrides): self
    {
        return new self(
            title: $overrides['title'] ?? $this->title,
            description: $overrides['description'] ?? $this->description,
            imageUrl: $overrides['imageUrl'] ?? $this->imageUrl,
            imageAlt: $overrides['imageAlt'] ?? $this->imageAlt,
            canonicalUrl: $overrides['canonicalUrl'] ?? $this->canonicalUrl,
            robots: $overrides['robots'] ?? $this->robots,
            type: $overrides['type'] ?? $this->type,
            siteName: $overrides['siteName'] ?? $this->siteName,
        );
    }

    /**
     * Whether the page asks to be indexed. Defaults to yes: a page that never
     * opted out of indexing should not be hidden by an empty robots value.
     */
    public function isIndexable(): bool
    {
        return ! str_contains(strtolower($this->robots), 'noindex');
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'imageUrl' => $this->imageUrl,
            'imageAlt' => $this->imageAlt,
            'canonicalUrl' => $this->canonicalUrl,
            'robots' => $this->robots,
            'type' => $this->type,
            'siteName' => $this->siteName,
        ];
    }

    private static function clean(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
