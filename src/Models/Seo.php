<?php

namespace DominionSolutions\FilamentSeo\Models;

use DominionSolutions\FilamentSeo\Support\SeoData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * The authored SEO metadata for one record.
 *
 * Rows are created lazily: a record with no row simply has no authored
 * metadata, and the owning model falls back to values generated from its own
 * attributes. That keeps the common case free of boilerplate rows.
 *
 * @property string|null $title
 * @property string|null $description
 * @property string|null $image_url
 * @property string|null $image_alt
 * @property string|null $canonical_url
 * @property string|null $robots
 * @property string|null $type
 */
class Seo extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'image_url',
        'image_alt',
        'canonical_url',
        'robots',
        'type',
    ];

    /**
     * The record this metadata belongs to.
     *
     * Typed as `Model` because the owner is whatever the morph points at —
     * there is no single related class to name here.
     *
     * @return MorphTo<Model, $this>
     */
    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Convert the stored row into the package's value object.
     */
    public function toSeoData(): SeoData
    {
        return new SeoData(
            title: $this->title,
            description: $this->description,
            imageUrl: $this->image_url,
            imageAlt: $this->image_alt,
            canonicalUrl: $this->canonical_url,
            robots: $this->robots,
            type: $this->type,
        );
    }
}
