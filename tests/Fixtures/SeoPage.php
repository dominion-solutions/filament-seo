<?php

namespace DominionSolutions\FilamentSeo\Tests\Fixtures;

use DominionSolutions\FilamentSeo\Concerns\HasSeoMetadata;
use Filament\Pages\Page;

/**
 * A public page that publishes its own metadata.
 *
 * Exists so Larastan analyses {@see HasSeoMetadata} in the context it is
 * actually used — a `Filament\Pages\Page` that declares `$seoTitle` and
 * `$seoDescription` and relies on the trait for the `<title>` element.
 */
class SeoPage extends Page
{
    use HasSeoMetadata;

    protected ?string $heading = null;

    protected static ?string $seoTitle = 'About Us';

    protected static ?string $seoDescription = 'Twenty years of engineering leadership, applied to software small businesses can rely on.';

    public function getView(): string
    {
        return 'seo-page';
    }
}
