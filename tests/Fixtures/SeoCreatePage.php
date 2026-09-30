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
 * Filament's resource pages are only generic from 5.4 onwards, so an
 * `@extends CreateRecord<...>` type argument would be invalid for 5.0-5.3, which
 * this package supports. PHPStan 2 does not require one, and the trait only ever
 * sees a `Model`.
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
