<?php

namespace DominionSolutions\FilamentSeo;

use Closure;
use DominionSolutions\FilamentSeo\Concerns\HasSeoMetadata;
use DominionSolutions\FilamentSeo\Support\MetaTags;
use DominionSolutions\FilamentSeo\Support\SeoData;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\View\PanelsRenderHook;

/**
 * Injects the package's metadata into a Filament panel's `<head>`.
 *
 *     ->plugins([
 *         FilamentSeoPlugin::make(),
 *     ])
 *
 * The plugin does not render the `<title>` element. Filament's layout renders
 * one from the page's title, which {@see HasSeoMetadata}
 * points at the SEO title — so enabling title rendering here would emit a
 * second, competing `<title>`. Set `filament-seo.render_title` to true only
 * when using a layout that emits no title of its own.
 */
class FilamentSeoPlugin implements Plugin
{
    protected ?string $renderHook = PanelsRenderHook::HEAD_END;

    protected bool $hasRenderHook = true;

    /**
     * Metadata for the whole panel, used when the current page does not
     * provide its own. Accepts a `SeoData`, an array of its arguments, or a
     * closure returning either.
     */
    protected SeoData|array|Closure|null $seoData = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filament-seo';
    }

    public function register(Panel $panel): void
    {
        if (! $this->hasRenderHook || $this->renderHook === null) {
            return;
        }

        $panel->renderHook($this->renderHook, function (): string {
            return MetaTags::render(
                $this->resolveSeoData(),
                withTitle: (bool) config('filament-seo.render_title', false),
            );
        });
    }

    public function boot(Panel $panel): void
    {
        //
    }

    /**
     * Render into a different hook, or disable injection with `null`.
     */
    public function renderHook(?string $hook): static
    {
        $this->renderHook = $hook;
        $this->hasRenderHook = $hook !== null;

        return $this;
    }

    /**
     * @param  SeoData | array<string, mixed> | Closure(): (SeoData|array<string, mixed>)|null  $data
     */
    public function seoData(SeoData|array|Closure|null $data): static
    {
        $this->seoData = $data;

        return $this;
    }

    /**
     * Work out the metadata for the page currently being rendered.
     */
    protected function resolveSeoData(): SeoData
    {
        $data = $this->panelSeoData() ?? $this->pageSeoData() ?? new SeoData;

        $data = $data->merge([
            'siteName' => config('filament-seo.site_name') ?? config('app.name'),
        ]);

        if (config('filament-seo.canonical_fallback', true)) {
            $data = $data->merge(['canonicalUrl' => url()->current()]);
        }

        return $data;
    }

    protected function panelSeoData(): ?SeoData
    {
        $data = $this->seoData;

        if ($data instanceof Closure) {
            $data = $data();
        }

        if ($data instanceof SeoData) {
            return $data;
        }

        if (is_array($data) && $data !== []) {
            return new SeoData(...$data);
        }

        return null;
    }

    /**
     * The metadata the current page published from its lifecycle hook.
     */
    protected function pageSeoData(): ?SeoData
    {
        return app(SeoRegistry::class)->get();
    }
}
