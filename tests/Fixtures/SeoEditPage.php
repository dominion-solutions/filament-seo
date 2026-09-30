<?php

namespace DominionSolutions\FilamentSeo\Tests\Fixtures;

use DominionSolutions\FilamentSeo\Filament\Concerns\UpdatesSeoMetadata;
use Filament\Resources\Pages\EditRecord;

/**
 * An `EditRecord` page that loads and persists the SEO section.
 *
 * Exists so Larastan analyses {@see UpdatesSeoMetadata} in the context it is
 * actually used, including that its overrides of `mutateFormDataBeforeFill()`
 * and `handleRecordUpdate()` stay signature-compatible with Filament's.
 *
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends EditRecord<TModel>
 */
class SeoEditPage extends EditRecord
{
    use UpdatesSeoMetadata;

    protected static string $resource = '';

    /**
     * @return array<string, mixed>
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
