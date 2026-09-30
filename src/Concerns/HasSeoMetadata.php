<?php

namespace DominionSolutions\FilamentSeo\Concerns;

use DominionSolutions\FilamentSeo\SeoRegistry;
use DominionSolutions\FilamentSeo\Support\SeoData;
use Illuminate\Contracts\Support\Htmlable;
use ReflectionProperty;

/**
 * Gives a Filament page its own clean title and meta description.
 *
 * A page declares its metadata as two optional properties, which keeps the
 * values next to the page class instead of buried in a Blade view:
 *
 *     protected static string $seoTitle = 'About Dominion Solutions';
 *     protected static string $seoDescription = 'Who we are and how we work.';
 *
 * `getTitle()` is overridden so Filament's layout renders the SEO title in its
 * existing `<title>` element. That is deliberate: it is the only way to control
 * the title in a Filament page, and it guarantees exactly one `<title>` tag
 * rather than a second one injected alongside Filament's.
 *
 * Because Filament falls back to `getTitle()` for a page's *visible* heading,
 * a page that renders a heading should set `$heading` (or override
 * `getHeading()`) so the heading is not replaced by the longer SEO title.
 */
trait HasSeoMetadata
{
    public function getTitle(): string|Htmlable
    {
        return $this->getSeoTitle();
    }

    /**
     * The page title, falling back to Filament's own default when the page has
     * not declared `$seoTitle`.
     */
    public function getSeoTitle(): string|Htmlable
    {
        return $this->getSeoProperty('seoTitle') ?? parent::getTitle();
    }

    public function getSeoDescription(): ?string
    {
        return $this->getSeoProperty('seoDescription');
    }

    public function getSeoImageUrl(): ?string
    {
        return $this->getSeoProperty('seoImageUrl');
    }

    public function getSeoImageAlt(): ?string
    {
        return $this->getSeoProperty('seoImageAlt');
    }

    /**
     * Defaults to null, which the renderer resolves to the current URL.
     */
    public function getSeoCanonicalUrl(): ?string
    {
        return $this->getSeoProperty('seoCanonicalUrl');
    }

    public function getSeoRobots(): ?string
    {
        return $this->getSeoProperty('seoRobots');
    }

    public function getSeoType(): ?string
    {
        return $this->getSeoProperty('seoType');
    }

    public function getSeoData(): SeoData
    {
        return new SeoData(
            title: $this->flattenSeoText($this->getSeoTitle()),
            description: $this->getSeoDescription(),
            imageUrl: $this->getSeoImageUrl(),
            imageAlt: $this->getSeoImageAlt(),
            canonicalUrl: $this->getSeoCanonicalUrl(),
            robots: $this->getSeoRobots(),
            type: $this->getSeoType(),
        );
    }

    /**
     * Flatten a page title to plain text.
     *
     * `getTitle()` may hand back an `Htmlable`, which cannot be cast to string
     * — doing so raises a fatal error rather than producing markup — so unwrap
     * it explicitly before it reaches the metadata.
     */
    protected function flattenSeoText(string|Htmlable $value): string
    {
        return $value instanceof Htmlable ? $value->toHtml() : $value;
    }

    /**
     * Publish the metadata for the render hook to pick up.
     *
     * Livewire calls this automatically, and it runs after `mount()` — so a page
     * that resolves its metadata from a record has that record available — and
     * before rendering.
     *
     * The `booted` + trait-name suffix is Livewire's trait hook convention, so a
     * page is free to define its own `booted()` without shadowing this.
     */
    public function bootedHasSeoMetadata(): void
    {
        app(SeoRegistry::class)->set($this->getSeoData());
    }

    /**
     * Read one of the optional metadata properties, if the page declared it.
     *
     * Reading through `property_exists()` rather than declaring the properties
     * in this trait keeps pages free to give them their own values: PHP
     * requires a class redeclaring a trait property to repeat the same default,
     * which would force every page to write an empty string first.
     *
     * Reflection rather than `$this->{$name}`, because pages may declare the
     * value as either a static or an instance property — and on a Livewire
     * component, reading a static property through `$this` falls through to
     * `__get()` and throws instead of returning the value.
     */
    protected function getSeoProperty(string $name): ?string
    {
        if (! property_exists($this, $name)) {
            return null;
        }

        $property = new ReflectionProperty($this, $name);
        $value = $property->isStatic() ? $property->getValue() : $property->getValue($this);

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
