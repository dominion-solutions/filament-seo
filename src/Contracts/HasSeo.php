<?php

namespace DominionSolutions\FilamentSeo\Contracts;

use DominionSolutions\FilamentSeo\Concerns\InteractsWithSeo;
use DominionSolutions\FilamentSeo\Models\Seo;
use DominionSolutions\FilamentSeo\Support\SeoData;

/**
 * The contract a model satisfies by using {@see InteractsWithSeo}.
 *
 * The trait supplies the implementation; this interface is the name the
 * package — and host applications — use to talk about a model that carries SEO
 * metadata. It exists so a value can be passed around, type hinted and
 * returned as "a model that can store SEO metadata" instead of a bare `Model`
 * that has to be checked at runtime.
 *
 * The `seo()` relation is deliberately not part of the contract: an interface
 * cannot know which model it will be mixed into, so naming the declaring model
 * in a generic would make the contract narrower than the trait that implements
 * it. The trait declares the precise `MorphOne<Seo, $this>`.
 */
interface HasSeo
{
    /**
     * The record's effective metadata: authored values layered over fallbacks.
     */
    public function getSeoData(): SeoData;

    /**
     * The authored metadata row, or null when nothing has been authored yet.
     */
    public function getSeoRecord(): ?Seo;

    /**
     * Create or update the authored metadata row from an array of attributes.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function saveSeo(array $attributes): Seo;
}
