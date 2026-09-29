<?php

namespace DominionSolutions\FilamentSeo\Support;

/**
 * Renders a `SeoData` into head markup.
 *
 * Deliberately free of Filament, HTTP and container dependencies so the
 * markup can be asserted in isolation. The caller resolves fallbacks (the
 * current URL for the canonical, `app.name` for the site name) and decides
 * whether the `<title>` element is emitted here — see `MetaTags::render()`.
 */
final class MetaTags
{
    /**
     * Render the metadata as head markup.
     *
     * `$withTitle` should be false whenever the host layout already renders a
     * `<title>` — as Filament does, from the page's title. Rendering a second
     * one is the exact bug this package exists to prevent.
     */
    public static function render(SeoData $data, bool $withTitle = false): string
    {
        $tags = [];

        if ($withTitle && filled($data->title)) {
            $tags[] = sprintf('<title>%s</title>', e($data->title));
        }

        if (filled($data->description)) {
            $tags[] = self::meta('name', 'description', $data->description);
        }

        if (filled($data->robots)) {
            $tags[] = self::meta('name', 'robots', $data->robots);
        }

        if (filled($data->canonicalUrl)) {
            $tags[] = sprintf('<link rel="canonical" href="%s" />', e($data->canonicalUrl));
        }

        $tags = [
            ...$tags,
            ...self::openGraphTags($data),
            ...self::twitterTags($data),
        ];

        return implode("\n", $tags);
    }

    /**
     * @return array<int, string>
     */
    private static function openGraphTags(SeoData $data): array
    {
        $tags = [self::meta('property', 'og:type', $data->type)];

        if (filled($data->title)) {
            $tags[] = self::meta('property', 'og:title', $data->title);
        }

        if (filled($data->description)) {
            $tags[] = self::meta('property', 'og:description', $data->description);
        }

        if (filled($data->canonicalUrl)) {
            $tags[] = self::meta('property', 'og:url', $data->canonicalUrl);
        }

        if (filled($data->siteName)) {
            $tags[] = self::meta('property', 'og:site_name', $data->siteName);
        }

        if (filled($data->imageUrl)) {
            $tags[] = self::meta('property', 'og:image', $data->imageUrl);
        }

        if (filled($data->imageAlt)) {
            $tags[] = self::meta('property', 'og:image:alt', $data->imageAlt);
        }

        return $tags;
    }

    /**
     * @return array<int, string>
     */
    private static function twitterTags(SeoData $data): array
    {
        $tags = [
            self::meta('name', 'twitter:card', filled($data->imageUrl) ? 'summary_large_image' : 'summary'),
        ];

        if (filled($data->title)) {
            $tags[] = self::meta('name', 'twitter:title', $data->title);
        }

        if (filled($data->description)) {
            $tags[] = self::meta('name', 'twitter:description', $data->description);
        }

        if (filled($data->imageUrl)) {
            $tags[] = self::meta('name', 'twitter:image', $data->imageUrl);
        }

        if (filled($data->imageAlt)) {
            $tags[] = self::meta('name', 'twitter:image:alt', $data->imageAlt);
        }

        return $tags;
    }

    private static function meta(string $attribute, string $key, ?string $content): string
    {
        return sprintf('<meta %s="%s" content="%s" />', $attribute, e($key), e($content));
    }
}
