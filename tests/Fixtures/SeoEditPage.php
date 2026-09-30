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
 * Filament's resource pages are only generic from 5.4 onwards, so an
 * `@extends EditRecord<...>` type argument would be invalid for 5.0-5.3, which
 * this package supports. PHPStan 2 does not require one, and the trait only ever
 * sees a `Model`.
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
