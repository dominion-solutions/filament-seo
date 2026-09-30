<?php

namespace DominionSolutions\FilamentSeo\Concerns;

use DominionSolutions\FilamentSeo\Contracts\HasSeo;
use DominionSolutions\FilamentSeo\Models\Seo;
use DominionSolutions\FilamentSeo\Support\SeoData;
use DominionSolutions\FilamentSeo\Support\Text;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Gives an Eloquent model authored, editor-managed SEO metadata with sensible
 * fallbacks generated from the model's own attributes.
 *
 * The layering is deliberate: a stored `Seo` row always wins, and the generated
 * fallbacks only fill the gaps. A record therefore has usable metadata the
 * moment it is created, and improves as soon as someone fills in the form.
 *
 * A model using this trait must also implement {@see HasSeo}, which is the
 * contract the trait implements:
 *
 *     class Product extends Model implements HasSeo
 *     {
 *         use InteractsWithSeo;
 *     }
 *
 * @phpstan-require-extends Model
 *
 * @phpstan-require-implements HasSeo
 */
trait InteractsWithSeo
{
    /**
     * The related metadata row.
     *
     * The `Seo` type argument is declared rather than inferred so static
     * analysis knows `first()` returns a `Seo` and `updateOrCreate()` returns
     * one, even though the model class is resolved from config at runtime.
     *
     * @return MorphOne<Seo, $this>
     */
    public function seo(): MorphOne
    {
        /** @var class-string<Seo> $model */
        $model = config('filament-seo.model', Seo::class);

        return $this->morphOne($model, 'seoable');
    }

    /**
     * The authored metadata row, or null when nothing has been authored yet.
     */
    public function getSeoRecord(): ?Seo
    {
        if ($this->relationLoaded('seo')) {
            $record = $this->getRelation('seo');

            return $record instanceof Seo ? $record : null;
        }

        return $this->seo()->first();
    }

    /**
     * The record's effective metadata: authored values layered over fallbacks.
     *
     * The fallbacks are built first and the stored row merged on top, so a field
     * an editor left blank keeps the generated value instead of going empty.
     */
    public function getSeoData(): SeoData
    {
        $fallbacks = new SeoData(
            title: $this->getSeoFallbackTitle(),
            description: $this->getSeoFallbackDescription(),
            imageUrl: $this->getSeoFallbackImageUrl(),
            imageAlt: $this->getSeoFallbackImageAlt(),
            canonicalUrl: $this->getSeoFallbackCanonicalUrl(),
            type: $this->getSeoFallbackType(),
        );

        $record = $this->getSeoRecord();

        if ($record === null) {
            return $fallbacks;
        }

        return $fallbacks->merge([
            'title' => $record->title,
            'description' => $record->description,
            'imageUrl' => $record->image_url,
            'imageAlt' => $record->image_alt,
            'canonicalUrl' => $record->canonical_url,
            'robots' => $record->robots,
            'type' => $record->type,
        ]);
    }

    /**
     * Create or update the authored metadata row from an array of attributes.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function saveSeo(array $attributes): Seo
    {
        $record = $this->seo()->firstOrNew();

        if (! $record->exists) {
            $record->robots = SeoData::ROBOTS_INDEX_FOLLOW;
            $record->type = SeoData::TYPE_WEBSITE;
        }

        $record->fill(array_intersect_key($attributes, array_flip([
            'title',
            'description',
            'image_url',
            'image_alt',
            'canonical_url',
        ])));

        if (array_key_exists('robots', $attributes)) {
            $record->robots = filled($attributes['robots']) ? $attributes['robots'] : SeoData::ROBOTS_INDEX_FOLLOW;
        }

        if (array_key_exists('type', $attributes)) {
            $record->type = filled($attributes['type']) ? $attributes['type'] : SeoData::TYPE_WEBSITE;
        }

        $record->save();

        return $record;
    }

    /**
     * Override to control the title used when none has been authored.
     */
    protected function getSeoFallbackTitle(): ?string
    {
        return $this->firstSeoAttribute(['name', 'title', 'headline']);
    }

    /**
     * Override to control the description used when none has been authored.
     */
    protected function getSeoFallbackDescription(): ?string
    {
        return Text::excerpt($this->firstSeoAttribute([
            'description',
            'summary',
            'excerpt',
            'tagline',
            'body',
            'content',
        ]));
    }

    /**
     * Override to supply a share image. The package stores no opinion on where
     * images live, so wire this to whatever the host already uses (a column, a
     * media library conversion, a CDN URL).
     */
    protected function getSeoFallbackImageUrl(): ?string
    {
        return $this->firstSeoAttribute(['og_image_url', 'image_url', 'share_image_url']);
    }

    /**
     * Override alongside `getSeoFallbackImageUrl()` to describe that image.
     */
    protected function getSeoFallbackImageAlt(): ?string
    {
        return null;
    }

    /**
     * Override when a record should not be canonical to the current URL.
     */
    protected function getSeoFallbackCanonicalUrl(): ?string
    {
        return null;
    }

    /**
     * Override to mark a record as an article rather than a plain page.
     */
    protected function getSeoFallbackType(): ?string
    {
        return null;
    }

    /**
     * The first non-blank string among the given attributes.
     *
     * @param  list<string>  $attributes
     */
    protected function firstSeoAttribute(array $attributes): ?string
    {
        foreach ($attributes as $attribute) {
            $value = $this->getAttribute($attribute);

            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        }

        return null;
    }
}
