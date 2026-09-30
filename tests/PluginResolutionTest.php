<?php

namespace DominionSolutions\FilamentSeo\Tests;

use DominionSolutions\FilamentSeo\FilamentSeoPlugin;
use DominionSolutions\FilamentSeo\SeoRegistry;
use DominionSolutions\FilamentSeo\Support\SeoData;
use DominionSolutions\FilamentSeo\Tests\Fixtures\TestablePlugin;

/**
 * Covers how the plugin decides which metadata a rendered page gets.
 *
 * @see FilamentSeoPlugin
 */
class PluginResolutionTest extends TestCase
{
    protected function publishPageData(?SeoData $data): void
    {
        app(SeoRegistry::class)->set($data);
    }

    public function test_a_page_with_no_metadata_resolves_to_an_empty_value_object(): void
    {
        $data = (new TestablePlugin)->resolvedSeoData();

        $this->assertNull($data->title);
        $this->assertNull($data->description);
    }

    public function test_the_page_wins_over_the_panel(): void
    {
        $this->publishPageData(new SeoData(title: 'About Us', description: 'Who we are.'));

        $plugin = (new TestablePlugin)->seoData([
            'title' => 'Acme',
            'description' => 'Panel wide description.',
        ]);

        $data = $plugin->resolvedSeoData();

        $this->assertSame('About Us', $data->title);
        $this->assertSame('Who we are.', $data->description);
    }

    public function test_the_panel_applies_when_the_page_publishes_nothing(): void
    {
        $plugin = (new TestablePlugin)->seoData([
            'title' => 'Acme',
            'robots' => SeoData::ROBOTS_NOINDEX_FOLLOW,
        ]);

        $data = $plugin->resolvedSeoData();

        $this->assertSame('Acme', $data->title);
        $this->assertSame(SeoData::ROBOTS_NOINDEX_FOLLOW, $data->robots);
    }

    public function test_the_site_name_falls_back_to_the_app_name(): void
    {
        $this->assertSame(
            config('app.name'),
            (new TestablePlugin)->resolvedSeoData()->siteName,
        );
    }

    public function test_the_configured_site_name_wins_over_the_app_name(): void
    {
        config()->set('filament-seo.site_name', 'Configured Site');

        $this->assertSame('Configured Site', (new TestablePlugin)->resolvedSeoData()->siteName);
    }

    public function test_a_page_can_still_set_its_own_site_name(): void
    {
        $this->publishPageData(new SeoData(siteName: 'Page Site'));

        $this->assertSame('Page Site', (new TestablePlugin)->resolvedSeoData()->siteName);
    }

    public function test_the_canonical_falls_back_to_the_current_url(): void
    {
        $this->assertSame(
            url()->current(),
            (new TestablePlugin)->resolvedSeoData()->canonicalUrl,
        );
    }

    public function test_the_canonical_fallback_can_be_turned_off(): void
    {
        config()->set('filament-seo.canonical_fallback', false);

        $this->assertNull((new TestablePlugin)->resolvedSeoData()->canonicalUrl);
    }

    public function test_a_pages_own_canonical_is_not_replaced_by_the_request_url(): void
    {
        $this->publishPageData(new SeoData(canonicalUrl: 'https://example.test/canonical'));

        $this->assertSame(
            'https://example.test/canonical',
            (new TestablePlugin)->resolvedSeoData()->canonicalUrl,
        );
    }

    public function test_panel_data_may_be_a_closure(): void
    {
        $plugin = (new TestablePlugin)->seoData(fn (): SeoData => new SeoData(title: 'From a closure'));

        $this->assertSame('From a closure', $plugin->resolvedSeoData()->title);
    }

    public function test_the_render_hook_omits_the_title_element_by_default(): void
    {
        $this->publishPageData(new SeoData(title: 'About Us'));

        $markup = (new TestablePlugin)->renderedMarkup();

        $this->assertStringNotContainsString('<title>', $markup);
        $this->assertStringContainsString('<meta property="og:title" content="About Us" />', $markup);
    }

    public function test_the_render_hook_includes_the_title_when_the_config_asks_for_it(): void
    {
        config()->set('filament-seo.render_title', true);
        $this->publishPageData(new SeoData(title: 'About Us'));

        $this->assertStringContainsString('<title>About Us</title>', (new TestablePlugin)->renderedMarkup(true));
    }
}
