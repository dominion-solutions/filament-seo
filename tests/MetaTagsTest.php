<?php

namespace DominionSolutions\FilamentSeo\Tests;

use DominionSolutions\FilamentSeo\Support\MetaTags;
use DominionSolutions\FilamentSeo\Support\SeoData;
use PHPUnit\Framework\TestCase;

class MetaTagsTest extends TestCase
{
    public function test_it_omits_the_title_element_by_default(): void
    {
        $markup = MetaTags::render(new SeoData(title: 'About', description: 'Who we are'));

        $this->assertStringNotContainsString('<title>', $markup);
    }

    public function test_it_renders_the_title_element_when_asked(): void
    {
        $markup = MetaTags::render(new SeoData(title: 'About'), withTitle: true);

        $this->assertStringContainsString('<title>About</title>', $markup);
    }

    public function test_it_renders_the_core_tags(): void
    {
        $markup = MetaTags::render(new SeoData(
            title: 'Products',
            description: 'Software we build and support.',
            canonicalUrl: 'https://dominion.solutions/products',
            siteName: 'Dominion Solutions',
        ));

        $this->assertStringContainsString('<meta name="description" content="Software we build and support." />', $markup);
        $this->assertStringContainsString('<meta name="robots" content="index, follow" />', $markup);
        $this->assertStringContainsString('<link rel="canonical" href="https://dominion.solutions/products" />', $markup);
        $this->assertStringContainsString('<meta property="og:title" content="Products" />', $markup);
        $this->assertStringContainsString('<meta property="og:site_name" content="Dominion Solutions" />', $markup);
        $this->assertStringContainsString('<meta name="twitter:card" content="summary" />', $markup);
    }

    public function test_it_omits_tags_whose_values_are_missing(): void
    {
        $markup = MetaTags::render(new SeoData);

        $this->assertStringNotContainsString('og:title', $markup);
        $this->assertStringNotContainsString('twitter:title', $markup);
        $this->assertStringNotContainsString('canonical', $markup);
        $this->assertStringNotContainsString('og:image', $markup);
    }

    public function test_a_share_image_upgrades_the_twitter_card(): void
    {
        $markup = MetaTags::render(new SeoData(
            imageUrl: 'https://dominion.solutions/image.jpg',
            imageAlt: 'Our office',
        ));

        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image" />', $markup);
        $this->assertStringContainsString('<meta property="og:image" content="https://dominion.solutions/image.jpg" />', $markup);
        $this->assertStringContainsString('<meta property="og:image:alt" content="Our office" />', $markup);
    }

    public function test_noindex_is_rendered_into_the_robots_tag(): void
    {
        $markup = MetaTags::render(new SeoData(robots: SeoData::ROBOTS_NOINDEX_FOLLOW));

        $this->assertStringContainsString('<meta name="robots" content="noindex, follow" />', $markup);
    }

    public function test_values_are_escaped(): void
    {
        $markup = MetaTags::render(new SeoData(title: 'Fish & "Chips"', description: '<script>alert(1)</script>'));

        $this->assertStringNotContainsString('<script>', $markup);
        $this->assertStringContainsString('Fish &amp; &quot;Chips&quot;', $markup);
    }
}
