<?php

namespace DominionSolutions\FilamentSeo\Tests\Fixtures;

use DominionSolutions\FilamentSeo\FilamentSeoPlugin;
use DominionSolutions\FilamentSeo\Support\MetaTags;
use DominionSolutions\FilamentSeo\Support\SeoData;

/**
 * The plugin with its resolution step exposed for testing.
 *
 * `resolveSeoData()` is protected because it is an implementation detail of the
 * render hook. Reaching it directly is the only way to assert the precedence
 * rules without standing up a full Filament panel and rendering a page.
 */
class TestablePlugin extends FilamentSeoPlugin
{
    /**
     * The metadata the plugin would render for the current request.
     */
    public function resolvedSeoData(): SeoData
    {
        return $this->resolveSeoData();
    }

    /**
     * The markup the plugin's render hook would emit.
     */
    public function renderedMarkup(bool $withTitle = false): string
    {
        return MetaTags::render($this->resolveSeoData(), withTitle: $withTitle);
    }
}
