<?php

namespace DominionSolutions\FilamentSeo\Filament\Concerns;

use DominionSolutions\FilamentSeo\Concerns\InteractsWithSeo;
use DominionSolutions\FilamentSeo\Contracts\HasSeo;
use DominionSolutions\FilamentSeo\Schemas\SeoSchema;
use DominionSolutions\FilamentSeo\Support\SeoData;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * Shared plumbing for Filament Create/Edit resource pages that use
 * {@see SeoSchema}.
 *
 * `SeoSchema` puts its fields under the `seo.*` state path, so the form data
 * arrives as one `['seo' => [...]]` array. This trait moves that array to and
 * from the record's related `Seo` row, keeping it out of the model's own
 * attributes.
 *
 * Compose it with {@see UpdatesSeoMetadata} on an EditRecord page or
 * {@see CreatesSeoMetadata} on a CreateRecord page.
 */
trait InteractsWithSeoForm
{
    /**
     * Hydrate the `seo` state from the record's existing metadata row.
     *
     * The `seo` key is always present, even for a record with no row yet, so the
     * nested fields receive their defaults and an editor sees a filled-in
     * "Robots" dropdown rather than a blank one.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function hydrateSeoFormData(array $data, ?Model $record): array
    {
        if (! $record instanceof Model) {
            return $data;
        }

        $this->ensureRecordInteractsWithSeo($record);

        $seo = $record->getSeoRecord();

        return [
            ...$data,
            'seo' => [
                'title' => $seo?->title,
                'description' => $seo?->description,
                'image_url' => $seo?->image_url,
                'image_alt' => $seo?->image_alt,
                'canonical_url' => $seo?->canonical_url,
                // `robots` and `type` are non-nullable columns, so `??` here
                // only ever covers a record that has no row yet. The operator
                // suppresses the error for the whole left-hand chain, so the
                // nullsafe `?->` would be redundant.
                'robots' => $seo->robots ?? SeoData::ROBOTS_INDEX_FOLLOW,
                'type' => $seo->type ?? SeoData::TYPE_WEBSITE,
            ],
        ];
    }

    /**
     * Persist the `seo` state, creating or updating the related row.
     *
     * @param  array<string, mixed>  $data
     */
    protected function saveSeoToRecord(Model $record, array $data): void
    {
        $this->ensureRecordInteractsWithSeo($record);

        $record->saveSeo($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function withoutSeoFormData(array $data): array
    {
        unset($data['seo']);

        return $data;
    }

    /**
     * Fail loudly when the model cannot carry SEO metadata.
     *
     * Declared as an unconditional assertion so static analysis treats the
     * record as a model implementing {@see HasSeo} from here on — which is
     * exactly what the guard enforces at runtime.
     *
     * @phpstan-assert Model&HasSeo $record
     */
    private function ensureRecordInteractsWithSeo(Model $record): void
    {
        if (in_array(InteractsWithSeo::class, class_uses_recursive($record), true)) {
            return;
        }

        throw new LogicException(sprintf(
            'The [%s] model must use the [%s] trait to save SEO metadata.',
            $record::class,
            InteractsWithSeo::class,
        ));
    }
}
