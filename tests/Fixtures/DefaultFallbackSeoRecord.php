<?php

namespace DominionSolutions\FilamentSeo\Tests\Fixtures;

use DominionSolutions\FilamentSeo\Concerns\InteractsWithSeo;
use DominionSolutions\FilamentSeo\Contracts\HasSeo;
use Illuminate\Database\Eloquent\Model;

/**
 * A model that relies entirely on the trait's own fallback behaviour.
 *
 * Companion to {@see SeoRecord}: this one declares nothing, so the attributes
 * the trait sniffs for — `name` for a title, `tagline` for a description — are
 * what get exercised.
 *
 * @property string|null $name
 * @property string|null $tagline
 * @property string|null $screenshot_url
 */
class DefaultFallbackSeoRecord extends Model implements HasSeo
{
    use InteractsWithSeo;

    protected $table = 'seo_records';

    protected $guarded = [];
}
