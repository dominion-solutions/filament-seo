<?php

namespace DominionSolutions\FilamentSeo\Tests;

use DominionSolutions\FilamentSeo\Support\SeoData;
use PHPUnit\Framework\TestCase;

class SeoDataTest extends TestCase
{
    public function test_blank_strings_are_normalised_to_null(): void
    {
        $data = new SeoData(
            title: '   ',
            description: '',
            imageUrl: "\n",
            siteName: '  ',
        );

        $this->assertNull($data->title);
        $this->assertNull($data->description);
        $this->assertNull($data->imageUrl);
        $this->assertNull($data->siteName);
    }

    public function test_values_are_trimmed(): void
    {
        $data = new SeoData(title: '  Dominion Solutions  ');

        $this->assertSame('Dominion Solutions', $data->title);
    }

    public function test_robots_and_type_fall_back_to_indexable_defaults(): void
    {
        $data = new SeoData;

        $this->assertSame(SeoData::ROBOTS_INDEX_FOLLOW, $data->robots);
        $this->assertSame(SeoData::TYPE_WEBSITE, $data->type);
        $this->assertTrue($data->isIndexable());
    }

    public function test_description_is_held_to_the_character_budget(): void
    {
        $data = new SeoData(description: str_repeat('a', 400));

        $this->assertLessThanOrEqual(160, mb_strlen((string) $data->description));
    }

    public function test_noindex_marks_the_page_as_not_indexable(): void
    {
        $this->assertFalse((new SeoData(robots: SeoData::ROBOTS_NOINDEX_FOLLOW))->isIndexable());
        $this->assertFalse((new SeoData(robots: SeoData::ROBOTS_NOINDEX_NOFOLLOW))->isIndexable());
    }

    public function test_merge_applies_only_non_null_overrides(): void
    {
        $base = new SeoData(title: 'Authored title', description: 'Authored description');

        $merged = $base->merge([
            'title' => null,
            'description' => 'Replacement description',
        ]);

        $this->assertSame('Authored title', $merged->title);
        $this->assertSame('Replacement description', $merged->description);
    }

    public function test_merge_layers_values_on_top_of_a_fallback_object(): void
    {
        $fallbacks = new SeoData(
            title: 'Fallback title',
            description: 'Fallback description',
            imageUrl: 'https://example.test/fallback.jpg',
        );

        $merged = $fallbacks->merge([
            'title' => 'Authored title',
            'imageAlt' => 'Authored alt',
        ]);

        $this->assertSame('Authored title', $merged->title);
        $this->assertSame('Fallback description', $merged->description);
        $this->assertSame('https://example.test/fallback.jpg', $merged->imageUrl);
        $this->assertSame('Authored alt', $merged->imageAlt);
    }

    public function test_to_array_round_trips_every_field(): void
    {
        $data = new SeoData(
            title: 'Title',
            description: 'Description',
            imageUrl: 'https://example.test/image.jpg',
            imageAlt: 'Alt',
            canonicalUrl: 'https://example.test/canonical',
            robots: SeoData::ROBOTS_NOINDEX_FOLLOW,
            type: SeoData::TYPE_ARTICLE,
            siteName: 'Site',
        );

        $this->assertSame([
            'title' => 'Title',
            'description' => 'Description',
            'imageUrl' => 'https://example.test/image.jpg',
            'imageAlt' => 'Alt',
            'canonicalUrl' => 'https://example.test/canonical',
            'robots' => SeoData::ROBOTS_NOINDEX_FOLLOW,
            'type' => SeoData::TYPE_ARTICLE,
            'siteName' => 'Site',
        ], $data->toArray());
    }
}
