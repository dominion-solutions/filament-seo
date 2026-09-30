<?php

namespace DominionSolutions\FilamentSeo\Tests\Fixtures;

use DominionSolutions\FilamentSeo\Filament\Concerns\CreatesSeoMetadata;
use Filament\Resources\Pages\CreateRecord;

/**
 * A `CreateRecord` page that persists the SEO section on create.
 *
 * Exists so Larastan analyses {@see CreatesSeoMetadata} in the context it is
 * actually used, including that its `handleRecordCreation()` override stays
 * signature-compatible with Filament's.
 *
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends CreateRecord<TModel>
 */
class SeoCreatePage extends CreateRecord
{
    use CreatesSeoMetadata;

    protected static string $resource = '';

    /**
     * @return array<string, mixed>
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
