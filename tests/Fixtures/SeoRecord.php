<?php

namespace DominionSolutions\FilamentSeo\Tests\Fixtures;

use DominionSolutions\FilamentSeo\Concerns\InteractsWithSeo;
use DominionSolutions\FilamentSeo\Contracts\HasSeo;
use DominionSolutions\FilamentSeo\Support\Text;
use Illuminate\Database\Eloquent\Model;

/**
 * A model that carries SEO metadata and generates its own fallbacks.
 *
 * Exists so Larastan analyses {@see InteractsWithSeo} in the context it is
 * actually used — an Eloquent model overriding the fallback hooks.
 *
 * @property string|null $name
 * @property string|null $tagline
 * @property string|null $screenshot_url
 */
class SeoRecord extends Model implements HasSeo
{
    use InteractsWithSeo;

    protected $table = 'seo_records';

    protected $guarded = [];

    protected function getSeoFallbackTitle(): ?string
    {
        return $this->name;
    }

    protected function getSeoFallbackDescription(): ?string
    {
        return Text::excerpt($this->tagline);
    }

    protected function getSeoFallbackImageUrl(): ?string
    {
        return $this->screenshot_url;
    }

    protected function getSeoFallbackImageAlt(): ?string
    {
        return 'A screenshot of this record';
    }

    protected function getSeoFallbackCanonicalUrl(): ?string
    {
        return null;
    }

    protected function getSeoFallbackType(): ?string
    {
        return null;
    }
}
