<?php

namespace DominionSolutions\FilamentSeo\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Wires SEO metadata into modal form actions — `CreateAction`, `EditAction`
 * and friends — rather than resource pages.
 *
 * Resource pages get {@see CreatesSeoMetadata} / {@see UpdatesSeoMetadata} and
 * nothing more. Modal actions are a different pipeline: the form state is
 * handed to the action, so the `seo` array has to be lifted out before the
 * record is written and saved once the record exists.
 *
 *     CreateAction::make()
 *         ->mutateDataUsing($this->captureSeoFormData(...))
 *         ->using(function (array $data): Model {
 *             $record = new ($this->getModel());
 *             $record->fill($data);
 *             $this->getRelationship()->save($record);
 *             $this->applyPendingSeoFormData($record);
 *
 *             return $record;
 *         })
 *
 * Pair it with `SeoSchema::make()` in the action's form, and on edit actions
 * use `fillForm($this->fillSeoFormData(...))` to load the existing values.
 */
trait InteractsWithSeoActions
{
    use InteractsWithSeoForm;

    /**
     * The SEO state lifted out of the most recent form submission.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $pendingSeoFormData = null;

    /**
     * For `mutateDataUsing()`: stash the `seo` state and hand the record's own
     * attributes to the action.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function captureSeoFormData(array $data): array
    {
        $this->pendingSeoFormData = is_array($data['seo'] ?? null) ? $data['seo'] : null;

        return $this->withoutSeoFormData($data);
    }

    /**
     * Persist the stashed SEO state against a record that has just been saved.
     */
    protected function applyPendingSeoFormData(Model $record): void
    {
        $data = $this->pendingSeoFormData;
        $this->pendingSeoFormData = null;

        if (is_array($data)) {
            $this->saveSeoToRecord($record, $data);
        }
    }

    /**
     * For `fillForm()`: add the record's stored SEO state to the form defaults.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function fillSeoFormData(array $data, ?Model $record = null): array
    {
        return $this->hydrateSeoFormData($data, $record);
    }
}
