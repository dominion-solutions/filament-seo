<?php

namespace DominionSolutions\FilamentSeo\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Loads and saves SEO metadata on a Filament `EditRecord` page.
 *
 *     class EditProduct extends EditProduct
 *     {
 *         use UpdatesSeoMetadata;
 *     }
 *
 * Pair with `SeoSchema::make()` in the resource's form.
 */
trait UpdatesSeoMetadata
{
    use InteractsWithSeoForm;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);

        /** @var Model $record */
        $record = $this->record;

        return $this->hydrateSeoFormData($data, $record);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $seo = $data['seo'] ?? null;

        $record = parent::handleRecordUpdate($record, $this->withoutSeoFormData($data));

        if (is_array($seo)) {
            $this->saveSeoToRecord($record, $seo);
        }

        return $record;
    }
}
