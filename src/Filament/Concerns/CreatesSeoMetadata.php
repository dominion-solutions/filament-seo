<?php

namespace DominionSolutions\FilamentSeo\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Saves SEO metadata on a Filament `CreateRecord` page.
 *
 *     class CreateProduct extends CreateProduct
 *     {
 *         use CreatesSeoMetadata;
 *     }
 *
 * Pair with `SeoSchema::make()` in the resource's form. Nothing needs loading
 * on a create form — a new record has no authored metadata yet — so only the
 * save side is handled here.
 */
trait CreatesSeoMetadata
{
    use InteractsWithSeoForm;

    /**
     * @param  array<string, mixed>  $data
     */
    public function handleRecordCreation(array $data): Model
    {
        $seo = $data['seo'] ?? null;

        $record = parent::handleRecordCreation($this->withoutSeoFormData($data));

        if (is_array($seo)) {
            $this->saveSeoToRecord($record, $seo);
        }

        return $record;
    }
}
